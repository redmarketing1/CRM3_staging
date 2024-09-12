<?php

namespace Modules\Taskly\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as FacadesResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use PDF;
use Modules\Taskly\Emails\EstimationForClientMail;
use App\Models\User;
use App\Models\Email;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Http\Controllers\InvoiceController;
use Modules\Taskly\Entities\Project;
use Modules\Taskly\Entities\ProjectEstimation;
use Modules\Taskly\Entities\EstimateQuote;
use Modules\Taskly\Entities\ProjectEstimationProduct;
use Modules\Taskly\Entities\ProjectProgressFiles;
use Modules\Taskly\Entities\ProjectProgress;
use Modules\Taskly\Entities\ProjectProgressMain;
use Modules\Taskly\Entities\EstimateQuoteItem;
use Modules\Taskly\Entities\ProjectClientFeedback;

class ProjectProgressController extends Controller
{
	public function list(Request $request)
	{
		$order          = $request->order;
		$column_array   = array(
			'id',
			'client_name',
			'comment',
			'name',
			'date'
		);

		$project_progress   = ProjectProgressMain::where('project_id', $request->project_id)->orderBy('id', "DESC");
		$total_count        = $project_progress->count();
		$items              = $project_progress->get();
		$progress_array     = array();

		foreach ($items as $item) {
			$username = isset($item->user->name) ? $item->user->name : '';
			$name_signature    = '<div class="progress-history-item">
                                    <span class="user-avatar">
                                        <i class="fa fa-user"></i>
                                        ' . $username . '
                                    </span>
                                    <span class="CellWithComment">
                                        <i class="fa fa-info-circle"></i>
                                        <span class="CellComment">
                                            <img src="' . $item->signature . '" />
                                        </span>
                                    </span>
                                </div>';

			$action = '<div class="action_btn">';
			$action .= '<div class=""><a href="' . route('progress.finalize', \Crypt::encrypt($item->id)) . '" class="" target="_blank" data-bs-whatever="' . __('View Progress') . '" data-bs-toggle="tooltip" data-bs-original-title="' . __('View Progress') . '"> <span class=""> <i class="ti ti-eye"></i></span></a></div>';
			$action .= '</div>';
			$row['id'] 			        = $item->id;
			$row['client_name'] 		= $item->name;
			$row['comment']             = $item->comment;
			$row['name'] 				= $name_signature;
			$row['date'] 				= company_datetime_formate($item->created_at);
			$row['action']  		    = $action;

			$progress_array[] 	= $row;
		}

		$response = array(
			"recordsTotal" => $total_count,
			"recordsFiltered" => $total_count,
			"data" => $progress_array,
		);

		return $response;
	}

	public function progressFinalize($id)
	{
		$id 					= Crypt::decrypt($id);
		$main_progress_id 		= $id;
		$progress_main_details 	= ProjectProgressMain::where('id', $id)->first();
		$estimation 			= ProjectEstimation::whereId($progress_main_details->estimation_id)->first();
		$quote 					= EstimateQuote::with("quoteItem")->where("project_estimation_id", $progress_main_details->estimation_id)->where("is_final", 1)->first();
		$project_estimation 	= ProjectEstimationProduct::where("project_estimation_id", $progress_main_details->estimation_id)->where("type", "item");
		$items 					= $project_estimation->get();

		$progressFinalizeEmailTemplate = getNotificationTemplateData('progress_finalize');

		$project 		= $estimation->project();
		$settings 		= getCompanyAllSetting($project->created_by, $project->workspace);
		$contractor 	= $quote->subContractor;
		$client 		= $project->client_data;
		$client_name 	= isset($client->name) ? $client->name : '';
		$client_email 	= isset($client->email) ? $client->email : '';

		$filters_request['order_by'] 	= array('field' => 'projects.created_at', 'order' => 'DESC');
		$project_record 				= Project::get_all($filters_request);
		$all_projects 					= isset($project_record['records']) ? $project_record['records'] : array();
		/*** with render use ****/
		$html 							= view('pdf.progress_finalize', compact('settings', 'items', 'main_progress_id', 'quote', 'contractor', 'client', 'client_name', 'client_email', 'project', 'estimation', 'progress_main_details'))->render();
		return view("taskly::projects.project_progress_finalize", compact('estimation', 'quote', 'settings', 'progressFinalizeEmailTemplate', 'html', 'main_progress_id', 'all_projects'));

		/*** without render use ****/
		// return view("project.project_progress_finalize", compact('estimation', 'quote', 'settings','progressFinalizeEmailTemplate', 'items','main_progress_id'));
	}

	public function estimationItem(Request $request)
	{
		$order          = $request->order;
		$column_array   = array(
			'id',
			'pos',
			'group_name',
			'name',
			'quantity',
			'price',
			'total_price',
			'',
			'',
			'',
			'',
		);
		$project_estimation = ProjectEstimationProduct::where("project_estimation_id", $request->estimation_id)->where("type", "item");
		$total_count = $project_estimation->count();
		if (!empty($order)) {
			$order_field =  $column_array[$order[0]['column']];
			$order_value = $order[0]['dir'];
			$project_estimation->orderBy($order_field, $order_value);
		}
		$items      = $project_estimation->get();

		if ($request->html == "true") {
			$estimationItem 			= $items;
			$estimation_products_arr 	= array();
			$confirm 					= 0;
			if (count($items) > 0) {
				foreach ($items as $item) {
					$sign_here 		= false;
					$progress_start = 0;
					$old_progress 	= '';
					$history 		= '<div class="progress-steps-wrapper"><div class="progress-steps">';
					$progress_files_gallery = '';
					if (count($item->progress_files) > 0) {
						foreach ($item->progress_files as $prow) {
							$progress_files_gallery .= view('taskly::project_progress.project_progress_files')->with(compact('prow'))->render();
						}
					}
					foreach ($item->progress()->orderBy('id')->get() as $progress) {
						$user_name = "";
						if (isset($progress->progress_id) && !empty($progress->progress_id)) {
							$user_name = ($progress->project_progress[0]['name']) ? '<i class="fa fa-user"></i> ' . $progress->project_progress[0]['name'] . '</i>' : '';
						}
						if ($progress->status == 0) {
							$sign_here = true;
						}
						if ($progress->status == 1) {
							$confirm++;
						}
						$progress_start = $progress->progress;

						$history .= '<div class="progress-step">';
						if ($progress->status == 1) {
							$history .= '<div class="status1 pstatus">';
						}
						if ($progress->status == 2) {
							$history .= '<div class="status2 pstatus">';
						}
						$progress_remarks = isset($progress->remarks) ? $progress->remarks : '';

						$loggedInUser = Auth::user()->name; // Erhalte den Namen des eingeloggten Benutzers
						$history .= '<div class="progress_wrapper"><div class="progress_labels"><div class="total_progress"><div class="progress-history-item"><span class="progress-date">' . date('d.m.y', strtotime($progress->created_at)) . '</span><span class="progress-percent">' . $progress->progress . '%</span><span class="user-avatar">' . $user_name . '</span>';

						if (isset($progress->signature) && !empty($progress->signature)) {
							$history .= '<span class="CellWithComment">
                                            <i class="fa fa-info-circle"></i>
                                            <span class="CellComment">
                                                <img src="' . $progress->signature . '" />
                                            </span>
                                        </span>';
						}

						// Füge den Span für progress_remarks nur hinzu, wenn progress_remarks vorhanden ist
						if (!empty($progress_remarks)) {
							$history .= '<span class="progress-comment-content">' . $progress_remarks . '</span>';
						}

						$history .= '</div></div></div></div>';

						$history .= '</div></div>';
						$old_progress = $progress;
					}
					$history .= '</div></div>';

					$item_old_progress 	= isset($old_progress->progress) ? $old_progress->progress : 0;
					$item_progress 		= '<div class="select-progress">
											<div class="select-progress-inner">
												<input class="progress" name="progress[' . $item->id . ']" type="range" id="progress-slider-' . $item->id . '" class="form-control"
													min="0" value="' . $item_old_progress . '" data-min="' . $item_old_progress . '" max="100" step="5" data-id="' . $item->id . '"
													style="width: 100%;">
												<span id="slider-value-' . $item->id . '" class="slider-value">' . $item_old_progress . '%</span>
											</div>
										</div>';

					$item_comment = '<textarea class="comment_text d-none" id="comment-' . $item->id . '" name="comments[' . $item->id . ']" placeholder="' . __('Comment ...') . '" data-id="' . $item->id . '"></textarea>';

					$progress_min_amount = isset($old_progress->progress_amount) ? $old_progress->progress_amount : 0;
					$item_progress_amount = '<input type="number" class="progress_amount form-control d-none" id="progress_amount_' . $item->id . '" min="' . $progress_min_amount . '" max="' . $item->quantity . '" value="' . (($progress_min_amount > 0) ? $progress_min_amount : '') . '" name="progress_amount[' . $item->id . ']" placeholder="' . __('Progress Amount ...') . '" data-id="' . $item->id . '" />';

					$item_progress_files = '<div class="progress_files d-none" data-id="' . $item->id . '"><div id="progressdropBox" ondrop="handleProgressDrop(event)" ondragover="handleProgressDragOver(event, this)" data-id="' . $item->id . '" data-estimationid="' . $item->project_estimation_id . '"><p style="font-size:20px ">Drag & Drop files here or click to select</p></div><input type="file" id="progressfileInput' . $item->id . '" class="progressfileInput" multiple onchange="handleProgressFileSelect(event, this)" data-id="' . $item->id . '" data-estimationid="' . $item->project_estimation_id . '" /></div>';

					$item_progress_id = isset($old_progress->id) ? $old_progress->id : 0;
					$item_progress_comment = isset($item->comment) ? $item->comment : "";
					$item_signature = '<div class="signature-field form-control"><input type="hidden" name="signatures[' . $item->id . ']" id="SignupImage' . $item->id . '" value=""><canvas id="items-signature-pad-' . $item->id . '" class="signature-pad" data-id="' . $item->id . '" height="100" width="300"></canvas></div> <input type="hidden" name="estimation_id" value="' . $item->project_estimation_id . '"><input type="hidden" name="progress_product_id" value="' . $item->id . '">';
					$item_signature .= '<div class="sign_btn_block mt-1">
                                            <div class="sign_btn_block_small">
                                            <button type="button" class=" btn btn-sm btn-danger clearSig" id="clearSig" data-id="' . $item->id . '"><i class="fa-regular fa-trash-can"></i></button>
                                            <button type="button" class=" btn btn-sm btn-danger commentSig" id="commentSig" data-id="' . $item->id . '"><i class="fa-regular fa-comment-dots"></i></button>
                                            <button type="button" class=" btn btn-sm btn-danger quantitySig" id="quantitySig" data-id="' . $item->id . '"><i class="fa-solid fa-hashtag"></i></button>
                                            <button type="button" class=" btn btn-sm btn-danger uploadSig" id="uploadSig" data-id="' . $item->id . '"><i class="fa-solid fa-camera"></i></button>
                                            </div>';

					$item_signature .= '</div>';

					$progress_files_preview = '<div class="progress_files_preview_' . $item->id . '"><div id="ProgressFilesPreviewContainer' . $item->id . '"></div></div>';

					$progress_files_remove_btn = '<div class="float-start d-flex">';
					if (Auth::user()->type == 'company') {
						$progress_files_remove_btn .= '<p class="text-muted d-none d-sm-flex align-items-center mb-0"><form method="POST" id="progress_bulk_delete_form">' . csrf_field() . '<input type="hidden" value="" name="remove_progress_files_ids" id="remove_progress_files_ids' . $item->id . '"><input type="hidden" name="estimation_id" value="' . $item->project_estimation_id . '"><button type="button" class="btn btn-sm btn-primary btn-icon show_confirm btn_progress_bulk_delete_files_' . $item->id . ' m-1 d-none"><i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="' . __('Delete Files') . '"></i> ' . __('Delete Files') . '</button></form></p>';
					}
					$progress_files_remove_btn .= '</div>';

					$progress_files_mediabox = '<div class="table-responsive mediabox item_mediabox_' . $item->id . '">' . $progress_files_gallery . '</div>';

					$row['product_id'] 			= '';
					$row['group'] 				= $item->group->group_name;
					$row['pos']                 = '<div class="div-inner">' . htmlspecialchars($item->pos) . '</div>';
					$row['name'] 				= $item->name;
					$row['description'] 		= '<div class="desc-inner">' . $item->description . '</div>';
					$row['quantity'] 			= $item->quantity . ' ' . $item->unit;
					$row['price'] 				= $item->price;
					$row['totalPrice'] 			= $item->total_price;
					$row['type'] 				= $item->type;
					$row['comment'] 			= $item->comment;
					$row['history'] 			= $history;
					$row['progress_amount']     = 0;
					$row['progress_remaining']  = 0;
					$row['item_signature'] 		= $item_progress . $item_comment . $item_progress_amount . $item_signature;
					$row['progress_item_id'] 			= $item->id;
					$row['item_files'] 		    = $item_progress_files . $progress_files_preview . $progress_files_mediabox . $progress_files_remove_btn;
					$row['_children'] 			= [['description' => $item->description]];
					$estimation_products_arr[] 	= $row;
				}
			}

			$response = array(
				"recordsTotal" => $total_count,
				"recordsFiltered" => $total_count,
				"data" => $estimation_products_arr,
				"confirm"   => $confirm
			);

			return $response;
		}
		return $items;
	}

	public function progressFileStore(Request $request)
	{
		if ((isset($request->product_id) && !empty($request->product_id)) && (isset($request->estimation_id) && !empty($request->estimation_id))) {
			$validator = Validator::make(
				$request->all(),
				[
					'files' => 'required',
				]
			);
			$progress_files_gallery = '';
			if ($validator->fails()) {
				$messages = $validator->getMessageBag();
				return response()->json(['status' => false, 'message' => $messages->first()]);
			} else {
				foreach ($request->file('files') as $file) {
					$file_request = new Request();
					$file_request->file = $file;
					$fileName = $request->estimation_id . time() . "_" . $file->getClientOriginalName();
					$url = '';
					$path = upload_file($file_request, 'file', $fileName, 'progress_files', []);
					if ($path['flag'] == 1) {
						$url = $path['url'];
					} else {
						return response()->json(['status' => false, 'message' => __($path['msg'])]);
					}
					$progress_files = new ProjectProgressFiles();
					$progress_files->estimation_id = $request->estimation_id;
					$progress_files->product_id = $request->product_id;
					$progress_files->file = $fileName;
					$progress_files->description = $request->description;
					$progress_files->save();
					$prow = '';
					$prow = array(
						'id' => $progress_files->id,
						'product_id' => $request->product_id,
						'file' => $fileName,
						'created_at' => date('d-m-Y', time())
					);
					$progress_files_gallery .= view('taskly::project_progress.project_progress_files')->with(compact('prow'))->render();
				}
				return response()->json(['status' => true, 'message' => __('File uploaded successfully.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''), 'html' => $progress_files_gallery]);
			}
		} else {
			return response()->json(['status' => false, 'message' => __('Something went wrong! Please try again later.')]);
		}
	}

	public function update(Request $request, ProjectProgress $projectProgress)
	{
		$return = array();
		$post_data = '';
		$post_data = $request->formdata;
		if (isset($request->confirm_signature) && !empty($request->confirm_signature)) {
			$progress_id = 0;
			$progress_confirmation 					= new ProjectProgressMain();
			$progress_confirmation->estimation_id 	= $request->estimation_id;
			$progress_confirmation->project_id 		= isset($request->project_id) ? Crypt::decrypt($request->project_id) : '';
			$progress_confirmation->user_id 		= isset($request->user_id) ? Crypt::decrypt($request->user_id) : '';
			$progress_confirmation->name 			= trim($request->confirm_user_name);
			$progress_confirmation->signature 		= $request->confirm_signature;
			$progress_confirmation->comment 		= trim($request->confirm_comment);
			$progress_confirmation->save();
			$progress_id = $progress_confirmation->id;
			if (isset($post_data) && !empty($post_data)) {
				/**** insert item details ****/
				foreach ($post_data as $key => $row) {
					if (isset($row['signature']) && !empty($row['signature'])) {
						$total_progress = 0;
						if ((isset($row['progress_amount']) && ($row['progress_amount'] != 'NaN')) && ($row['progress_amount'] > $row['progress_old_qty'])) {
							$total_progress = ($row['progress_amount'] / $row['progress_total_qty']) * 100;
						} else {
							$total_progress = isset($row['progress']) ? $row['progress'] : 0;
						}
						ProjectProgress::create([
							"estimation_id" => $request->estimation_id,
							"progress_id" 	=> $progress_id,
							'product_id' 	=> $key,
							'progress' 		=> $total_progress,
							'progress_amount' => isset($row['progress_amount']) ? trim($row['progress_amount']) : 0,
							'remarks' 		=> isset($row['comment']) ? $row['comment'] : '',
							'signature' 	=> isset($row['signature']) ? $row['signature'] : '',
							"status" 		=> 1,
							"approve_date" 	=> date("Y-m-d H:i:s"),
						]);
					}
				}
				/*** after save the progress details progress confirm and generate invoice ***/
				$dir_path = storage_path('fonts/');
				$company_details = getCompanyAllSetting();
				if (!is_dir($dir_path)) {
					mkdir($dir_path, 0777);
				}
				if (isset($request->estimation_id) && $request->estimation_id != "") {
					$estimation = ProjectEstimation::find($request->estimation_id);
					if (isset($estimation->project_id) && $estimation->project_id != "") {
						Project::count_progress($estimation->project_id);
					}
					$estimation 	= ProjectEstimation::find($request->estimation_id);
					$products 		= ProjectEstimationProduct::with("progress")->where("project_estimation_id", $request->estimation_id)->where("type", "item")->get();
					$progressArray 	= [];
					$quote 			= EstimateQuote::where("project_estimation_id", $request->estimation_id)->where("is_final", 1)->first();
					$quoteItem 		= EstimateQuoteItem::where("estimate_quote_id", $quote->id)->whereHas("progress", function ($query) {
						$query->where("status", 1);
					})->with('projectEstimationProduct')->get();
					/*** check if only comment added then do not generate invoice ****/
					$generate_new_invoice = 0;
					foreach ($quoteItem as $item) {
						$progress 			= ProjectProgress::where("product_id", $item->product_id)->where("status", 1)->orderBy("progress", "desc")->first();
						$done_progress 		= ProjectProgress::where("product_id", $item->product_id)->where("status", 2)->orderBy("progress", "desc")->first();
						$latest_progress 	= isset($progress) ? $progress->progress : 0;
						$previous_progress 	= isset($done_progress) ? $done_progress->progress : 0;
						$new_progress 		= floatval($latest_progress) - floatval($previous_progress);
						if ($new_progress > 0) {
							$generate_new_invoice = 1;
						}
					}

					/*** generate invoice if any progress ****/
					if ($generate_new_invoice == 1) {
						$invoice = ["tax" => $quote->tax, 'discount' => $quote->discount];
						$invoiceController 	= new InvoiceController();
						$invoice_id 		= Invoice::create([
							"invoice_id" 			=> $invoiceController->invoiceNumber(),
							'account_type' 			=> "Taskly",
							'issue_date' 			=> date("Y-m-d H:i:s"),
							'due_date' 				=> date("Y-m-d H:i:s", strtotime("+7Days")),
							'send_date' 			=> date("Y-m-d H:i:s"),
							'client' 				=> $estimation->project()->client,
							'project' 				=> $estimation->project()->id,
							'type' 					=> __('Progress'),
							'tax' 					=> $quote->tax == 19 ? 1 : 0,
							'discount' 				=> $quote->discount,
							'project_estimation_id' => $request->estimation_id,
							'invoice_template' 		=> 'template11',
							'workspace' 			=> getActiveWorkSpace(),
							'created_by' 			=> Auth::user()->id,
						])->id;
						foreach ($quoteItem as $item) {
							$data 				= ["name" => $item->name];
							$progress 			= ProjectProgress::where("product_id", $item->product_id)->where("status", 1)->orderBy("progress", "desc")->first();
							$done_progress 		= ProjectProgress::where("product_id", $item->product_id)->where("status", 2)->orderBy("progress", "desc")->first();
							$latest_progress 	= isset($progress) ? $progress->progress : 0;
							$previous_progress 	= isset($done_progress) ? $done_progress->progress : 0;
							$new_progress 		= floatval($latest_progress) - floatval($previous_progress);
							$price 				= $item->price;
							$done_price 		= 0;
							$done_total_price 	= 0;
							if ($new_progress > 0) {
								$done_price 		= $price * $new_progress / 100;
								$done_total_price 	= $item->projectEstimationProduct->quantity * floatval($done_price);
							}
							$data['done_progress'] 	= ($done_progress) ? $done_progress->progress : 0;
							$data['progress'] 		= ($progress) ? $progress->progress : 0;
							$data['cal_progress'] 	= $data['progress'] - $data['done_progress'];
							$data['total_price'] 	= $item->total_price;
							$data["amount"] 		= round($item->total_price * $data['cal_progress'] / 100, 2);
							$invoice["item"][] 		= $data;
							$invoiceProduct 				= new InvoiceProduct();
							$invoiceProduct->invoice_id 	= $invoice_id;
							$invoiceProduct->item 			= isset($item->projectEstimationProduct->name) ? $item->projectEstimationProduct->name : '';
							$invoiceProduct->quantity 		= $item->projectEstimationProduct->quantity;
							$invoiceProduct->price 			= $done_price;
							$invoiceProduct->total_price 	= $done_total_price;
							$invoiceProduct->tax 			= $quote->tax == 19 ? 1 : 0;
							$invoiceProduct->product_type 	= __('progress');
							$invoiceProduct->description 	= $item->projectEstimationProduct->description;
							$invoiceProduct->progress 		= $data['cal_progress'];
							$invoiceProduct->progress_amount = $data["amount"];
							$invoiceProduct->save();
						}
						$file_name = "";
						$estimation_file_name = $estimation->title . ' - ' . $estimation->project()->title;
						if (isset($estimation->project()->construction_detail->address_1)) {
							$estimation_file_name .= ' - ' . $estimation->project()->construction_detail->address_1;
						}
						if (isset($estimation->project()->construction_detail->city)) {
							$estimation_file_name .= ' - ' . $estimation->project()->construction_detail->city;
						}
						$estimation_file_name .= ' - #1' . $estimation->id . ' - ' . $company_details['company_name'];
						$file_name = $estimation_file_name . '.pdf';

						$content = $this->pdf($invoice_id);
						$content['file_name'] = $file_name;

						$path = $this->generatePDF($content);
						$path2 = $this->generateProgressPDF($content);
						foreach ($products as $product) {
							$productData = [];
							// Initialize progress values to 0 for each percentage
							for ($i = 10; $i <= 100; $i += 10) {
								$productData[$i] = (object)['progress' => 0, 'created_at' => "", 'approve_date' => ""];
							}
							foreach ($product->progress()->where('status', ">", 0)->get() as $progress) {
								// Update the corresponding progress value
								$productData[round($progress->progress)] = (object)[
									'progress' => 1,
									'created_at' => date("m/d/Y", strtotime($progress->created_at)),
									'approve_date' => $progress->approve_date
								];
							}
							// Add the product data to the progress array
							$progressArray[$product->name] = $productData;
						}
						$content["products"] = $progressArray;
						$content["project"] = $estimation->project();
						$html = view('pdf.progress', compact('content'))->render();
						$client_name = isset($content["client"]->name) ? $content["client"]->name : '';
						$subject = "Rechnung " . Invoice::invoiceNumberFormat($content["settings"], $content["invoice"]->invoice) . " - BV " . $estimation->project()->location . " " . $client_name . " - " . $company_details['company_name'];
						$emailData = (object) [
							"subject" => $subject,
							"sender_name" => env("APP_NAME"),
							"content" => $content,
							'pdf' => $path,
							'progress_pdf' => $path2,
							'cc' => null,
							"sender" => env("MAIL_FROM_ADDRESS"),
							"view" => 'pdf.progress'
						];
						$email = Email::create([
							'subject' => $subject ? $subject : "",
							"message" => $html,
							"status" => 1,
							'attachments' => $path . ', ' . $path2,
							"project_id" => $estimation->project_id,
							"type" => "App\Models\ProjectEstimation",
							"type_id" => $products[0]->project_estimation_id
						]);
						$client = $estimation->project()->client_data;
						/*** send email with attached PDF of progress to logged in user ***/
						$sender = User::find(Auth::user()->id);
						$sender->sentEmails()->save($email);

						$setconfing =  SetConfigEmail();
						$smtp_error = [];
						if ($setconfing ==  true) {
							try {
								Mail::to($sender->email)->send(new EstimationForClientMail($emailData));
								/*** send email with attached PDF of progress to client ***/
								if (isset($client->id)) {
									$recipient = User::find($client->id);
									if (isset($recipient->id)) {
										$recipient->receivedEmails()->save($email);
									}
									Mail::to($recipient->email)->send(new EstimationForClientMail($emailData));
								}
							} catch (\Exception $e) {
								return response()->json(['status' => false, 'message' => $e->getMessage()]);
							}
						}

						ProjectProgress::where("estimation_id", $request->estimation_id)->where("status", 1)->update([
							"status" => 2,
						]);
					}
					return response()->json(['status' => true, 'message' => __('Progress confirm succesfully.')]);
				} else {
					return response()->json(['status' => false, 'message' => __('Progress not confirm.')]);
				}
			} else {
				return response()->json(['status' => false, 'message' => __('Please fill the details or signature.')]);
			}
		} else {
			return response()->json(['status' => false, 'message' => __('Please do confirmation signature.')]);
		}
	}

	public function pdf($invoiceId)
	{
		$settings 		  = getCompanyAllSetting();
		$invoice          = Invoice::where('id', $invoiceId)->first();
		$invoice->invoice = $invoice->invoice_id;
		$invoice->project_name = $invoice->projects->name;
		$data             = DB::table('settings');
		$data             = $data->where('created_by', $invoice->created_by);
		$data             = $data->where('workspace', $invoice->workspace);
		$data1            = $data->get();

		foreach ($data1 as $row) {
			$settings[$row->key] = $row->value;
		}

		$client = $invoice->clients;

		if (isset($client->id)) {
			$client->company_name = isset($client->company_name) ? $client->company_name : '';
			$client->mobile       = isset($client->mobile_no) ? $client->mobile_no : '';
			$client->address      = isset($client->address_1) ? $client->address_1 : '';
			$client->zip          = isset($client->zip_code) ? $client->zip_code : '';
			$client->city         = isset($client->city) ? $client->city : '';
			$client->state        = isset($client->state) ? $client->state : '';
			$client->country      = isset($client->country) ? $client->country : '';
		}

		$items         	= [];
		$totalTaxPrice 	= 0;
		$totalQuantity 	= 0;
		$totalRate     	= 0;
		$totalDiscount 	= 0;
		$totalPayable 	= 0;
		$taxesData     	= [];

		foreach ($invoice->items as $product) {
			$estimation_product = isset($product->estimation_product) ? $product->estimation_product : '';
			$project_progress = $product->project_progress() ?? '';

			$item              = new \stdClass();
			$item->pos         = isset($estimation_product->pos) ? $estimation_product->pos : '';
			$item->group_name  = isset($estimation_product->group->group_name) ? $estimation_product->group->group_name : '';
			$item->comment     = isset($project_progress->remarks) ? $project_progress->remarks : "";
			$item->last_signature   = isset($project_progress->signature) ? $project_progress->signature : '';
			$item->user_name   = isset($project_progress->main_progress->name) ? $project_progress->main_progress->name : '';
			$item->name        = $product->item;
			$item->quantity    = $product->quantity;
			$item->unit         = isset($estimation_product->unit) ? $estimation_product->unit : '';
			$item->tax         = (isset($product->tax) && $product->tax > 0) ? $product->tax : '';
			$item->discount    = $product->discount;
			$item->price       = $product->price;
			$item->description = $product->description;
			$item->total_price = $product->total_price;
			$item->progress = $product->progress;
			$totalPayable   += $item->payable = $product->total_price * $product->progress / 100;

			$totalQuantity += $item->quantity;
			$totalRate     += $item->price;
			$totalDiscount += $item->payable * $invoice->discount / 100;

			$item->progress_list = $product->project_all_progress();
			$item->progress_files = $product->progress_files();

			$itemTaxes = [];
			if (!empty($item->tax)) {
				$taxes = Invoice::tax($item->tax);
				if (count($taxes) > 0) {
					foreach ($taxes as $tax) {
						$taxPrice      = Invoice::taxRate($tax->rate, $item->price, $item->quantity);
						$totalTaxPrice += $taxPrice;

						$itemTax['name']  = $tax->name;
						$itemTax['rate']  = $tax->rate . '%';
						$itemTax['price'] = priceFormat($settings, $taxPrice);
						$itemTaxes[]      = $itemTax;

						if (array_key_exists($tax->name, $taxesData)) {
							$taxesData[$tax->name] = $taxesData[$tax->name] + $taxPrice;
						} else {
							$taxesData[$tax->name] = $taxPrice;
						}
					}
				}
			} else {
				$item->itemTax = [];
			}
			$item->itemTax = $itemTaxes;
			$items[]       = $item;
		}

		$tax = $invoice->tax == 1 ? 19 : 0;
		$invoice->items         = $items;
		$invoice->totalTaxPrice = $totalTaxPrice;
		$invoice->totalQuantity = $totalQuantity;
		$invoice->totalRate     = $totalRate;
		$invoice->totalDiscount = $totalDiscount;
		$invoice->totalPayable = $totalPayable;
		$invoice->taxesData     = $taxesData;
		$invoice->totalTax    = $totalPayable * $tax / 100;

		if ($invoice) {
			$color      = '#' . $settings['invoice_color'];
			$font_color = getFontColor($color);

			return [
				'invoice' => $invoice, 'color' => $color,
				'settings' => $settings, 'client' => $client,  'font_color' => $font_color
			];
		} else {
			return redirect()->back()->with('error', __('Permission denied.'));
		}
	}

	public function generatePDF($data)
	{
		$dir 	= "uploads/quotes/";

		//Set your logo
		$company_logo = get_file(sidebar_logo());
		$company_settings = getCompanyAllSetting($data["invoice"]->created_by, $data["invoice"]->workspace);
		$invoice_logo = isset($company_settings['invoice_logo']) ? $company_settings['invoice_logo'] : '';
		if (isset($invoice_logo) && !empty($invoice_logo)) {
			$data['img']  = get_file($invoice_logo);
		} else {
			$data['img']  = $company_logo;
		}
		$data["settings"]['footer_title'] = isset($company_settings['invoice_footer_title']) ? $company_settings['invoice_footer_title'] : '';
		$data["settings"]['footer_notes'] = isset($company_settings['invoice_footer_notes']) ? $company_settings['invoice_footer_notes'] : '';

		$pdf 	= PDF::loadView('invoice.templates.' . $data["invoice"]['invoice_template'], $data)->setOptions(['defaultFont' => 'sans-serif']);

		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}
		$invoice_id = Invoice::invoiceNumberFormat($data["settings"], $data["invoice"]->invoice);
		$filename = 'invoice-' . $invoice_id . "-" . time() . '.pdf';
		$dir .= $filename;
		$pdf->save($dir);
		return $dir;
	}

	public function generateProgressPDF($data)
	{
		$dir 	= "uploads/quotes/progress/";
		$pdf = PDF::loadView('pdf.progress_data', $data)->setPaper('a4', 'landscape');

		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}
		$filename = isset($data["file_name"]) ? $data['file_name'] : 'Project Progress (' . $data["invoice"]->project_name . ').pdf';
		$dir .= $filename;
		$pdf->save($dir);
		return $dir;
	}

	public function sendProgressFinalizeClient(Request $request)
	{
		ini_set("max_execution_time", "-1");
		ini_set("memory_limit", "-1");
		$main_progress_id 		= $request->id;
		$progress_main_details 	= ProjectProgressMain::where('id', $request->id)->first();
		$estimation 			= ProjectEstimation::whereId($progress_main_details->estimation_id)->first();
		$project 				= $estimation->project();
		$quote 					= EstimateQuote::with("quoteItem")->where("project_estimation_id", $progress_main_details->estimation_id)->where("is_final", 1)->first();
		$project_estimation 	= ProjectEstimationProduct::where("project_estimation_id", $progress_main_details->estimation_id)->where("type", "item");
		$items 					= $project_estimation->get();
		$settings 				= getCompanyAllSetting();
		$client 				= $project->client_data;
		if ($request->type == "pdf" || $request->type == "email") {
			$constructionDetail 			= isset($project->construction_detail) ? $project->construction_detail : null;
			$contractor 					= $quote->subContractor;
			$path 							= 'quotes/';
			$client_name 					= isset($client->name) ? $client->name : '';
			$client_email 					= isset($request->client_email) ? $request->client_email : '';
			$clientCompanyName 				= '';
			$clientSalutationTitle 			= '';
			$clientAcademicTitle 			= '';
			$clientFirstName 				= '';
			$clientLastName 				= '';
			$clientEmail 					= '';
			$clientPhone 					= '';
			$clientMobile 					= '';
			$clientWebsite 					= '';
			$constructionStreetN 			= '';
			$constructionAdditionalAddress 	= '';
			$constructionZipcode 			= '';
			$constructionCity 				= '';
			$constructionState 				= '';
			$constructionCountry 			= '';
			$constructionTaxNumber 			= '';
			$constructionTaxNotes 			= '';
			$clientSalutation 				= "";
			$constructionSalutation 		= "";
			if (isset($constructionDetail) && $constructionDetail != null) {
				$clientCompanyName = (isset($client->company_name) && !empty($client->company_name)) ? $client->company_name : '';
				$clientSalutationTitle = (isset($client->salutation) && !empty($client->salutation)) ? $client->salutation : '';
				$clientAcademicTitle = (isset($client->title) && !empty($client->title)) ? $client->title : '';
				$clientFirstName = (isset($client->first_name) && !empty($client->first_name)) ? $client->first_name : '';
				$clientLastName = (isset($client->last_name) && !empty($client->last_name)) ? $client->last_name : '';
				$clientEmail = (isset($client->email) && !empty($client->email)) ? $client->email : '';
				$clientPhone = (isset($client->phone) && !empty($client->phone)) ? $client->phone : '';
				$clientMobile = (isset($client->mobile) && !empty($client->mobile)) ? $client->mobile : '';
				$clientWebsite = (isset($client->website) && !empty($client->website)) ? $client->website : '';
				$constructionStreetN = (isset($constructionDetail->address_1) && !empty($constructionDetail->address_1)) ? $constructionDetail->address_1 : '';
				$constructionAdditionalAddress = (isset($constructionDetail->address_2) && !empty($constructionDetail->address_2)) ? $constructionDetail->address_2 : '';
				$constructionZipcode = (isset($constructionDetail->zipcode) && !empty($constructionDetail->zipcode)) ? $constructionDetail->zipcode : '';
				$constructionCity = (isset($constructionDetail->city) && !empty($constructionDetail->city)) ? $constructionDetail->city : '';
				$constructionState = (isset($constructionDetail->state) && !empty($constructionDetail->state)) ? $constructionDetail->state : '';
				$constructionCountry = (isset($constructionDetail->country) && !empty($constructionDetail->country) && (isset($constructionDetail->countryDetail) && $constructionDetail->countryDetail != null)) ? $constructionDetail->countryDetail->name : '';
				$constructionTaxNumber = (isset($constructionDetail->tax_number) && !empty($constructionDetail->tax_number)) ? $constructionDetail->tax_number : '';
				$constructionTaxNotes = (isset($constructionDetail->notes) && !empty($constructionDetail->notes)) ? $constructionDetail->notes : '';
				if (isset($client->salutation) && $client->salutation == 'Mr.') {
					$clientSalutation = __("Dear");
				} else if (isset($client->salutation) && $client->salutation == 'Ms.') {
					$clientSalutation = __("Dear");
				};
				if (isset($constructionDetail->salutation) && $constructionDetail->salutation == 'Mr.') {
					$constructionSalutation = __("Dear");
				} else if (isset($constructionDetail->salutation) && $constructionDetail->salutation == 'Ms.') {
					$constructionSalutation = __("Dear");
				};
			}
			$allVariable = [
				"{client_name}",
				"{estimation.title}",
				"{client.company_name}",
				"{client.salutation_title}",
				"{client.academic_title}",
				"{client.first_name}",
				"{client.last_name}",
				"{client.email}",
				"{client.phone}",
				"{client.mobile}",
				"{client.website}",
				"{construction.street}",
				"{construction.additional_address}",
				"{construction.zipcode}",
				"{construction.city}",
				"{construction.state}",
				"{construction.country}",
				"{construction.tax_number}",
				"{construction.notes}",
				"{current.date+21days}",
				"{client.salutation}",
				"{construction.salutation}",
			];
			$allVariabelValues = [
				$client_name,
				$estimation->title,
				$clientCompanyName,
				$clientSalutationTitle,
				$clientAcademicTitle,
				$clientFirstName,
				$clientLastName,
				$clientEmail,
				$clientPhone,
				$clientMobile,
				$clientWebsite,
				$constructionStreetN,
				$constructionAdditionalAddress,
				$constructionZipcode,
				$constructionCity,
				$constructionState,
				$constructionCountry,
				$constructionTaxNumber,
				$constructionTaxNotes,
				date("m/d/Y", strtotime("+21days")),
				$clientSalutation,
				$constructionSalutation,
			];
			$subject 		= $request->subject;
			$subject 		= str_replace($allVariable, $allVariabelValues, $subject);
			$message 		= $request->email_text;
			$message 		= str_replace($allVariable, $allVariabelValues, $message);
			$extra_notes 	= $request->extra_notes;
			$extra_notes 	= str_replace($allVariable, $allVariabelValues, $extra_notes);
			$pdfTopNotes 	= $request->pdf_top_notes;
			$pdfTopNotes 	= str_replace($allVariable, $allVariabelValues, $pdfTopNotes);
			$site_money_format = site_money_format();
		//	$estimation_file_name = $estimation->title . ' - ' . $project->title;
			$estimation_file_name = __('Project Progress') . ' - ' . date('d.m.y',strtotime($progress_main_details->created_at));
			if (isset($project->construction_detail->address_1)) {
				$estimation_file_name .= ' - ' . $project->construction_detail->address_1;
			}
			if (isset($project->construction_detail->city)) {
				$estimation_file_name .= ' - ' . $project->construction_detail->city;
			}
			$estimation_file_name .= ' - #1' . $estimation->id . ' - ' . $settings['company_name'];

			$file_name = $estimation_file_name . '.pdf';
			$data = [
				'estimation' 		=> $estimation,
				'settings' 			=> $settings,
				'quote' 			=> $quote,
				'client' 			=> $client,
				'client_name' 		=> $client_name,
				'client_email' 		=> $client_email,
				'message' 			=> $message,
				'extra_notes' 		=> $extra_notes,
				'pdfTopNotes' 		=> $pdfTopNotes,
				'contractor' 		=> $contractor,
				'project' 			=> $project,
				'site_money_format' => $site_money_format,
				'path' 				=> $path,
				'file_name' 		=> $file_name,
				'items' 			=> $items,
				'main_progress_id' 	=> $main_progress_id,
				'progress_main_details' => $progress_main_details,
			];
			if ($request->type == "pdf") {
				return $this->generateProgressFinalizePDF($data, true);
			}
			$dir = $this->generateProgressFinalizePDF($data);
			$cc_email = $request->cc_email;
			$bcc_email = $request->bcc_email;
			if (isset($request->copy_to_company) && $request->copy_to_company == true) {
				$bcc_email[] = $settings['company_email'];
			}
			if (isset($request->copy_to_subcontractor) && $request->copy_to_subcontractor == true) {
				if (isset($quote->subContractor->email) && ($quote->subContractor->email != '')) {
					$bcc_email[] = $settings['company_email'];
				}
			}
			$emailData = (object)[
				"subject" => $subject,
				"sender_name" => env("APP_NAME"),
				"content" => $message,
				'pdf' => $dir,
				'cc' => $cc_email,
				'bcc' => $bcc_email,
				"sender" => env("MAIL_FROM_ADDRESS"),
				"view" => 'email.common',
			];

			$email = Email::create([
				'subject' => $subject ? $subject : "",
				"message" => $message,
				"status" => 1,
				'attachments' => $path,
				"project_id" => $estimation->project_id,
				"type" => "App\Models\EstimateQuote",
				"type_id" => $quote->id,
				"estimations" => json_encode(['quote' => $quote, 'items' => $quote->quoteItem()->get()])
			]);
			$sender = User::find(Auth::user()->id);
			$sender->sentEmails()->save($email);
			if (isset($client->user_id)) {
				$recipient = User::find($client->user_id);
				if (isset($recipient->id)) {
					$recipient->receivedEmails()->save($email);
				}
			}
			$setconfing =  SetConfigEmail();
			$smtp_error = [];
			if ($setconfing ==  true) {
				try {
					Mail::to($client_email)->send(new EstimationForClientMail($emailData));
				} catch (\Exception $e) {
					return response(['status' => false, 'message' => $e->getMessage()]);
				}
			}
			
			if ($request->type == "email") {
				$fileName = "";
				if (File::exists($dir)) {
					$dir_path = 'uploads/files/';
					if (!is_dir($dir_path)) {
						mkdir($dir_path, 0777);
					}
					$fileName = $file_name;
					$new_path = $dir_path . $file_name;
					File::copy($dir, $new_path);
				}
				$new_message = "Project Progress Finalize  : " . $quote->title;
				$client_message = isset($message) ? $message : $new_message;

				$feedback = new ProjectClientFeedback();
				$feedback->project_id = $estimation->project_id;
				$feedback->file = isset($fileName) ? $fileName : '';
				$feedback->feedback = $client_message;
				$feedback->feedback_by = Auth::user()->id;
				$feedback->save();

				$success_message = 'Email successfully sent';
			} else {
				$success_message = 'PDF download successfully.';
			}
		}
		return response(['status' => true, 'message' => $success_message]);
	}

	public function generateProgressFinalizePDF($data, $download = false){
        $dir 	= "uploads/project_progress_finalize/";
        $pdf = PDF::loadView('pdf.progress_finalize', $data)->setPaper('a4');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = isset($data["file_name"]) ? $data['file_name'] : 'Project Progress (' . $data["invoice"]->project_name . ').pdf';
        $dir .= $filename;
        if ($download) {
            return $pdf->download($filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        }
        $pdf->save($dir);
        return $dir;
    }
}
