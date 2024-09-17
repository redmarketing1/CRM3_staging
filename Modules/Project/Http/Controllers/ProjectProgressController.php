<?php

namespace Modules\Project\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Project\Entities\Project;
use Modules\Taskly\Entities\ProjectEstimation;
use Modules\Taskly\Entities\ProjectProgressMain;

class ProjectProgressController extends Controller
{
    public function index(Project $project)
    {
        $active_estimation = ProjectEstimation::where('project_id', $project->id)
            ->where('is_active', 1)->first();

        $site_money_format = site_money_format();

        return view('project::project.show.progress.index', compact(
            'project',
            'site_money_format',
            'active_estimation',
        ));
    } 
    
    public function store(Request $request)
    {
        $request->validate([
            'progress_confirm'   => 'required',
            'progress_comment'   => 'required',
            'progress_signature' => 'required',
        ]);

        $projectProgressMain = ProjectProgressMain::create([
            // 'estimation_id' => $request->estimation_id,
            'project_id' => $request->project_id,
            'user_id'    => auth()->id(),
            'name'       => auth()->user()->name,
            'signature'  => $request->progress_signature,
            'comment'    => $request->progress_comment,
        ]);

        // if (isset($post_data) && ! empty($post_data)) {
        //     $this->insertItemDetails($post_data, $request, $progress_id);
        //     $this->handleProgressAndInvoiceGeneration($request);
        // } 

        return redirect()
            ->back()
            ->with('success', __('Project progress has successfully.'));
    }

    private function insertItemDetails($post_data, $request, $progress_id)
    {
        foreach ($post_data as $key => $row) {
            if (isset($row['signature']) && ! empty($row['signature'])) {
                $total_progress = $this->calculateTotalProgress($row);
                ProjectProgress::create([
                    "estimation_id"   => $request->estimation_id,
                    "progress_id"     => $progress_id,
                    'product_id'      => $key,
                    'progress'        => $total_progress,
                    'progress_amount' => isset($row['progress_amount']) ? trim($row['progress_amount']) : 0,
                    'remarks'         => isset($row['comment']) ? $row['comment'] : '',
                    'signature'       => isset($row['signature']) ? $row['signature'] : '',
                    "status"          => 1,
                    "approve_date"    => now(),
                ]);
            }
        }
    }

    private function calculateTotalProgress($row)
    {
        if (isset($row['progress_amount']) && $row['progress_amount'] != 'NaN' && $row['progress_amount'] > $row['progress_old_qty']) {
            return ($row['progress_amount'] / $row['progress_total_qty']) * 100;
        } else {
            return isset($row['progress']) ? $row['progress'] : 0;
        }
    }

    private function handleProgressAndInvoiceGeneration($request)
    {
        $estimation = ProjectEstimation::find($request->estimation_id);
        if ($estimation && $estimation->project_id) {
            Project::count_progress($estimation->project_id);
        }

        $quote      = EstimateQuote::where("project_estimation_id", $request->estimation_id)->where("is_final", 1)->first();
        $quoteItems = $this->getQuoteItemsWithProgress($quote);

        if ($this->shouldGenerateNewInvoice($quoteItems)) {
            $invoice_id = $this->generateInvoice($request, $estimation, $quote, $quoteItems);
            $this->generatePDFAndEmail($invoice_id, $estimation, $quoteItems);
        }
    }

    private function getQuoteItemsWithProgress($quote)
    {
        return EstimateQuoteItem::where("estimate_quote_id", $quote->id)
            ->whereHas("progress", function ($query) {
                $query->where("status", 1);
            })
            ->with('projectEstimationProduct')->get();
    }

    private function shouldGenerateNewInvoice($quoteItems)
    {
        foreach ($quoteItems as $item) {
            $latest_progress = ProjectProgress::where("product_id", $item->product_id)
                ->where("status", 1)
                ->orderBy("progress", "desc")
                ->first();

            $done_progress = ProjectProgress::where("product_id", $item->product_id)
                ->where("status", 2)
                ->orderBy("progress", "desc")
                ->first();

            $new_progress = floatval($latest_progress->progress ?? 0) - floatval($done_progress->progress ?? 0);

            if ($new_progress > 0) {
                return true;
            }
        }

        return false;
    }

    private function generateInvoice($request, $estimation, $quote, $quoteItems)
    {
        $invoiceController = new InvoiceController();
        $invoice_id        = Invoice::create([
            "invoice_id"            => $invoiceController->invoiceNumber(),
            'account_type'          => "Taskly",
            'issue_date'            => now(),
            'due_date'              => now()->addDays(7),
            'client'                => $estimation->project->client,
            'project'               => $estimation->project->id,
            'type'                  => __('Progress'),
            'tax'                   => $quote->tax == 19 ? 1 : 0,
            'discount'              => $quote->discount,
            'project_estimation_id' => $request->estimation_id,
            'invoice_template'      => 'template11',
            'workspace'             => getActiveWorkSpace(),
            'created_by'            => Auth::user()->id,
        ])->id;

        $this->storeInvoiceProducts($invoice_id, $quoteItems, $quote);

        return $invoice_id;
    }

    private function storeInvoiceProducts($invoice_id, $quoteItems, $quote)
    {
        foreach ($quoteItems as $item) {
            $progress      = ProjectProgress::where("product_id", $item->product_id)->where("status", 1)->orderBy("progress", "desc")->first();
            $done_progress = ProjectProgress::where("product_id", $item->product_id)->where("status", 2)->orderBy("progress", "desc")->first();

            $new_progress     = floatval($progress->progress ?? 0) - floatval($done_progress->progress ?? 0);
            $done_price       = ($item->price * $new_progress) / 100;
            $done_total_price = $item->projectEstimationProduct->quantity * $done_price;

            InvoiceProduct::create([
                'invoice_id'      => $invoice_id,
                'item'            => $item->projectEstimationProduct->name ?? '',
                'quantity'        => $item->projectEstimationProduct->quantity,
                'price'           => $done_price,
                'total_price'     => $done_total_price,
                'tax'             => $quote->tax == 19 ? 1 : 0,
                'product_type'    => __('progress'),
                'description'     => $item->projectEstimationProduct->description,
                'progress'        => $new_progress,
                'progress_amount' => round($item->total_price * $new_progress / 100, 2),
            ]);
        }
    }

    private function generatePDFAndEmail($invoice_id, $estimation, $quoteItems)
    {
        $content              = $this->pdf($invoice_id);
        $content['file_name'] = $this->generatePDFFileName($estimation);
        $path                 = $this->generatePDF($content);
        $this->sendInvoiceEmail($content, $estimation, $quoteItems, $path);
    }

    private function generatePDFFileName($estimation)
    {
        $file_name = $estimation->title . ' - ' . $estimation->project->title;

        if ($estimation->project->construction_detail->address_1) {
            $file_name .= ' - ' . $estimation->project->construction_detail->address_1;
        }
        if ($estimation->project->construction_detail->city) {
            $file_name .= ' - ' . $estimation->project->construction_detail->city;
        }

        $file_name .= ' - #1' . $estimation->id . ' - ' . getCompanyAllSetting()['company_name'];

        return $file_name . '.pdf';
    }


}