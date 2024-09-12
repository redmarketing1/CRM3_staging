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

        return view('project::project.progress.index', compact(
            'project',
            'site_money_format',
            'active_estimation',
        ));
    }

    /**
     * Summary of store
     * @param \Modules\Project\Http\Controllers\Request $request
     * @param \Modules\Project\Http\Controllers\ProjectProgress $projectProgress
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    // public function store(Request $request, ProjectProgress $projectProgress)
    // {
    // 	$return = array();
    // 	$post_data = '';
    // 	$post_data = $request->formdata;

    // 	if (isset($request->confirm_signature) && !empty($request->confirm_signature)) {

    // 		$progress_id = 0;
    // 		$progress_confirmation 					= new ProjectProgressMain();
    // 		$progress_confirmation->estimation_id 	= $request->estimation_id;
    // 		$progress_confirmation->project_id 		= isset($request->project_id) ? Crypt::decrypt($request->project_id) : '';
    // 		$progress_confirmation->user_id 		= isset($request->user_id) ? Crypt::decrypt($request->user_id) : '';
    // 		$progress_confirmation->name 			= trim($request->confirm_user_name);
    // 		$progress_confirmation->signature 		= $request->confirm_signature;
    // 		$progress_confirmation->comment 		= trim($request->confirm_comment);
    // 		$progress_confirmation->save();

    // 		$progress_id = $progress_confirmation->id;

    // 		if (isset($post_data) && !empty($post_data)) {
    // 			/**** insert item details ****/
    // 			foreach ($post_data as $key => $row) {
    // 				if (isset($row['signature']) && !empty($row['signature'])) {
    // 					$total_progress = 0;
    // 					if ((isset($row['progress_amount']) && ($row['progress_amount'] != 'NaN')) && ($row['progress_amount'] > $row['progress_old_qty'])) {
    // 						$total_progress = ($row['progress_amount'] / $row['progress_total_qty']) * 100;
    // 					} else {
    // 						$total_progress = isset($row['progress']) ? $row['progress'] : 0;
    // 					}
    // 					ProjectProgress::create([
    // 						"estimation_id" => $request->estimation_id,
    // 						"progress_id" 	=> $progress_id,
    // 						'product_id' 	=> $key,
    // 						'progress' 		=> $total_progress,
    // 						'progress_amount' => isset($row['progress_amount']) ? trim($row['progress_amount']) : 0,
    // 						'remarks' 		=> isset($row['comment']) ? $row['comment'] : '',
    // 						'signature' 	=> isset($row['signature']) ? $row['signature'] : '',
    // 						"status" 		=> 1,
    // 						"approve_date" 	=> date("Y-m-d H:i:s"),
    // 					]);
    // 				}
    // 			}
    // 			/*** after save the progress details progress confirm and generate invoice ***/
    // 			$dir_path = storage_path('fonts/');
    // 			$company_details = getCompanyAllSetting();
    // 			if (!is_dir($dir_path)) {
    // 				mkdir($dir_path, 0777);
    // 			}
    // 			if (isset($request->estimation_id) && $request->estimation_id != "") {
    // 				$estimation = ProjectEstimation::find($request->estimation_id);
    // 				if (isset($estimation->project_id) && $estimation->project_id != "") {
    // 					Project::count_progress($estimation->project_id);
    // 				}
    // 				$estimation 	= ProjectEstimation::find($request->estimation_id);
    // 				$products 		= ProjectEstimationProduct::with("progress")->where("project_estimation_id", $request->estimation_id)->where("type", "item")->get();
    // 				$progressArray 	= [];
    // 				$quote 			= EstimateQuote::where("project_estimation_id", $request->estimation_id)->where("is_final", 1)->first();
    // 				$quoteItem 		= EstimateQuoteItem::where("estimate_quote_id", $quote->id)->whereHas("progress", function ($query) {
    // 					$query->where("status", 1);
    // 				})->with('projectEstimationProduct')->get();
    // 				/*** check if only comment added then do not generate invoice ****/
    // 				$generate_new_invoice = 0;
    // 				foreach ($quoteItem as $item) {
    // 					$progress 			= ProjectProgress::where("product_id", $item->product_id)->where("status", 1)->orderBy("progress", "desc")->first();
    // 					$done_progress 		= ProjectProgress::where("product_id", $item->product_id)->where("status", 2)->orderBy("progress", "desc")->first();
    // 					$latest_progress 	= isset($progress) ? $progress->progress : 0;
    // 					$previous_progress 	= isset($done_progress) ? $done_progress->progress : 0;
    // 					$new_progress 		= floatval($latest_progress) - floatval($previous_progress);
    // 					if ($new_progress > 0) {
    // 						$generate_new_invoice = 1;
    // 					}
    // 				}

    // 				/*** generate invoice if any progress ****/
    // 				if ($generate_new_invoice == 1) {
    // 					$invoice = ["tax" => $quote->tax, 'discount' => $quote->discount];
    // 					$invoiceController 	= new InvoiceController();
    // 					$invoice_id 		= Invoice::create([
    // 						"invoice_id" 			=> $invoiceController->invoiceNumber(),
    // 						'account_type' 			=> "Taskly",
    // 						'issue_date' 			=> date("Y-m-d H:i:s"),
    // 						'due_date' 				=> date("Y-m-d H:i:s", strtotime("+7Days")),
    // 						'send_date' 			=> date("Y-m-d H:i:s"),
    // 						'client' 				=> $estimation->project()->client,
    // 						'project' 				=> $estimation->project()->id,
    // 						'type' 					=> __('Progress'),
    // 						'tax' 					=> $quote->tax == 19 ? 1 : 0,
    // 						'discount' 				=> $quote->discount,
    // 						'project_estimation_id' => $request->estimation_id,
    // 						'invoice_template' 		=> 'template11',
    // 						'workspace' 			=> getActiveWorkSpace(),
    // 						'created_by' 			=> Auth::user()->id,
    // 					])->id;
    // 					foreach ($quoteItem as $item) {
    // 						$data 				= ["name" => $item->name];
    // 						$progress 			= ProjectProgress::where("product_id", $item->product_id)->where("status", 1)->orderBy("progress", "desc")->first();
    // 						$done_progress 		= ProjectProgress::where("product_id", $item->product_id)->where("status", 2)->orderBy("progress", "desc")->first();
    // 						$latest_progress 	= isset($progress) ? $progress->progress : 0;
    // 						$previous_progress 	= isset($done_progress) ? $done_progress->progress : 0;
    // 						$new_progress 		= floatval($latest_progress) - floatval($previous_progress);
    // 						$price 				= $item->price;
    // 						$done_price 		= 0;
    // 						$done_total_price 	= 0;
    // 						if ($new_progress > 0) {
    // 							$done_price 		= $price * $new_progress / 100;
    // 							$done_total_price 	= $item->projectEstimationProduct->quantity * floatval($done_price);
    // 						}
    // 						$data['done_progress'] 	= ($done_progress) ? $done_progress->progress : 0;
    // 						$data['progress'] 		= ($progress) ? $progress->progress : 0;
    // 						$data['cal_progress'] 	= $data['progress'] - $data['done_progress'];
    // 						$data['total_price'] 	= $item->total_price;
    // 						$data["amount"] 		= round($item->total_price * $data['cal_progress'] / 100, 2);
    // 						$invoice["item"][] 		= $data;
    // 						$invoiceProduct 				= new InvoiceProduct();
    // 						$invoiceProduct->invoice_id 	= $invoice_id;
    // 						$invoiceProduct->item 			= isset($item->projectEstimationProduct->name) ? $item->projectEstimationProduct->name : '';
    // 						$invoiceProduct->quantity 		= $item->projectEstimationProduct->quantity;
    // 						$invoiceProduct->price 			= $done_price;
    // 						$invoiceProduct->total_price 	= $done_total_price;
    // 						$invoiceProduct->tax 			= $quote->tax == 19 ? 1 : 0;
    // 						$invoiceProduct->product_type 	= __('progress');
    // 						$invoiceProduct->description 	= $item->projectEstimationProduct->description;
    // 						$invoiceProduct->progress 		= $data['cal_progress'];
    // 						$invoiceProduct->progress_amount = $data["amount"];
    // 						$invoiceProduct->save();
    // 					}
    // 					$file_name = "";
    // 					$estimation_file_name = $estimation->title . ' - ' . $estimation->project()->title;
    // 					if (isset($estimation->project()->construction_detail->address_1)) {
    // 						$estimation_file_name .= ' - ' . $estimation->project()->construction_detail->address_1;
    // 					}
    // 					if (isset($estimation->project()->construction_detail->city)) {
    // 						$estimation_file_name .= ' - ' . $estimation->project()->construction_detail->city;
    // 					}
    // 					$estimation_file_name .= ' - #1' . $estimation->id . ' - ' . $company_details['company_name'];
    // 					$file_name = $estimation_file_name . '.pdf';

    // 					$content = $this->pdf($invoice_id);
    // 					$content['file_name'] = $file_name;

    // 					$path = $this->generatePDF($content);
    // 					$path2 = $this->generateProgressPDF($content);
    // 					foreach ($products as $product) {
    // 						$productData = [];
    // 						// Initialize progress values to 0 for each percentage
    // 						for ($i = 10; $i <= 100; $i += 10) {
    // 							$productData[$i] = (object)['progress' => 0, 'created_at' => "", 'approve_date' => ""];
    // 						}
    // 						foreach ($product->progress()->where('status', ">", 0)->get() as $progress) {
    // 							// Update the corresponding progress value
    // 							$productData[round($progress->progress)] = (object)[
    // 								'progress' => 1,
    // 								'created_at' => date("m/d/Y", strtotime($progress->created_at)),
    // 								'approve_date' => $progress->approve_date
    // 							];
    // 						}
    // 						// Add the product data to the progress array
    // 						$progressArray[$product->name] = $productData;
    // 					}
    // 					$content["products"] = $progressArray;
    // 					$content["project"] = $estimation->project();
    // 					$html = view('pdf.progress', compact('content'))->render();
    // 					$client_name = isset($content["client"]->name) ? $content["client"]->name : '';
    // 					$subject = "Rechnung " . Invoice::invoiceNumberFormat($content["settings"], $content["invoice"]->invoice) . " - BV " . $estimation->project()->location . " " . $client_name . " - " . $company_details['company_name'];
    // 					$emailData = (object) [
    // 						"subject" => $subject,
    // 						"sender_name" => env("APP_NAME"),
    // 						"content" => $content,
    // 						'pdf' => $path,
    // 						'progress_pdf' => $path2,
    // 						'cc' => null,
    // 						"sender" => env("MAIL_FROM_ADDRESS"),
    // 						"view" => 'pdf.progress'
    // 					];
    // 					$email = Email::create([
    // 						'subject' => $subject ? $subject : "",
    // 						"message" => $html,
    // 						"status" => 1,
    // 						'attachments' => $path . ', ' . $path2,
    // 						"project_id" => $estimation->project_id,
    // 						"type" => "App\Models\ProjectEstimation",
    // 						"type_id" => $products[0]->project_estimation_id
    // 					]);
    // 					$client = $estimation->project()->client_data;
    // 					/*** send email with attached PDF of progress to logged in user ***/
    // 					$sender = User::find(Auth::user()->id);
    // 					$sender->sentEmails()->save($email);

    // 					$setconfing =  SetConfigEmail();
    // 					$smtp_error = [];
    // 					if ($setconfing ==  true) {
    // 						try {
    // 							Mail::to($sender->email)->send(new EstimationForClientMail($emailData));
    // 							/*** send email with attached PDF of progress to client ***/
    // 							if (isset($client->id)) {
    // 								$recipient = User::find($client->id);
    // 								if (isset($recipient->id)) {
    // 									$recipient->receivedEmails()->save($email);
    // 								}
    // 								Mail::to($recipient->email)->send(new EstimationForClientMail($emailData));
    // 							}
    // 						} catch (\Exception $e) {
    // 							return response()->json(['status' => false, 'message' => $e->getMessage()]);
    // 						}
    // 					}

    // 					ProjectProgress::where("estimation_id", $request->estimation_id)->where("status", 1)->update([
    // 						"status" => 2,
    // 					]);
    // 				}
    // 				return response()->json(['status' => true, 'message' => __('Progress confirm succesfully.')]);
    // 			} else {
    // 				return response()->json(['status' => false, 'message' => __('Progress not confirm.')]);
    // 			}
    // 		} else {
    // 			return response()->json(['status' => false, 'message' => __('Please fill the details or signature.')]);
    // 		}
    // 	} else {
    // 		return response()->json(['status' => false, 'message' => __('Please do confirmation signature.')]);
    // 	}
    // }


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