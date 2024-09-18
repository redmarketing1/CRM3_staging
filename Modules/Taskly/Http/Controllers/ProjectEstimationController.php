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
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use PDF;
use Modules\Taskly\Exports\ProjectEstimationExport;
use Modules\Taskly\Emails\EstimationForClientMail;
use Modules\Taskly\Emails\CommonEmailTemplate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use App\Models\User;
use App\Models\Email;
use App\Models\SmartTemplate;
use App\Models\SmartPromptQueue;
use Modules\Taskly\Entities\Project;
use Modules\Taskly\Entities\ProjectFile;
use Modules\Taskly\Entities\ProjectEstimation;
use Modules\Taskly\Entities\EstimateQuote;
use Modules\Taskly\Entities\EstimationGroup;
use Modules\Taskly\Entities\ProjectEstimationProduct;
use Modules\Taskly\Entities\EstimateQuoteItem;
use Modules\Taskly\Entities\ProjectClientFeedback;
use Maatwebsite\Excel\Facades\Excel;
use Batch;

class ProjectEstimationController extends Controller
{
	protected $userService;
    protected $chatgptService;
    protected $keey_checking= true;
    protected $items_array      = array();
    protected $item_name        = array();
    protected $item_description = array();
    protected $item_string      = "";
    protected $item_pos         = array();
    protected $parent_pos       = array();
    protected $group_name       = array();
    protected $parent_node      = array();
    protected $parent_key       = 0;

	public function index(Request $request) {
		$user 			= Auth::user();
		if ($user->isAbleTo('estimation manage')) {
			$query        	= ProjectEstimation::select("*")->with(['getProjectDetail', 'final_quote']);
			if ($user->type == 'company') {
				$query->where('created_by', '=', $user->id);
			} else {
				$project_estimation_ids = EstimateQuote::where('user_id', $user->id)->pluck('project_estimation_id')->toArray();
				$query->whereIn("id", $project_estimation_ids);
			}
			$estimations        = $query->get();
			$estimationStatus 	= ProjectEstimation::$statues;
			$statuesColor 		= ProjectEstimation::$statuesColor;
			return view('taskly::project_estimations.index', compact('estimations','estimationStatus','statuesColor'));
		} else {
			return redirect()->back()->with('error', __('Permission Denied.'));
		}
    }

	public function all_data(Request $request)
    {
		$user 			= Auth::user();
		$search         = $request->search;
		$start          = intval($request->start);
		$length         = intval($request->length);
		$order          = $request->order;

		$estimationStatus = ProjectEstimation::$statues;
        $statuesColor = ProjectEstimation::$statuesColor;

		$column_array   = array(
							'project_estimations.status',
							'',
							'project_estimations.title',
							'project_estimations.net_with_discount',
							'project_estimations.gross_with_discount',
							'project_estimations.discount',
							'project_estimations.issue_date',
						);

        $query        = ProjectEstimation::select("*");
        $query->with(['getProjectDetail', 'final_quote']);

		$filter_count = $total_count = $query->count();

		if (!empty($order)) {
			$order_field =  $column_array[$order[0]['column']];
			$order_value = $order[0]['dir'];
			$query->orderBy($order_field,$order_value);
		}
		$record     = $query->skip($start)->take($length)->get();

        foreach ($record as $key => $estimation) {
			$action = '<div class="action_btn icons-div">';
			if($user->type == 'company') {
				$action .='<div class="action-btn btn-primary ms-2">
								<a class="action-btn btn-info mx-1  btn btn-sm d-inline-flex align-items-center"
									data-ajax-popup="true" data-size="lg"
									data-title="'. __('Create New Item') .'"
									data-url="'. route('estimations.copy', $estimation->id).'"
									data-toggle="tooltip" title="'. __('Duplicate Estimation') .'"><i
										class="ti ti-copy text-white"></i></a>
							</div>';

				$action .='<div class="btn btn-sm bg-danger ms-2" style="padding-top: 4px!important;">
				<form id="delete-form-'. $estimation->id .'"
					action="'. route('estimations.deleteEstimation', [$estimation->id]) .'" method="POST">
					<input type="hidden" name="_token" value="'. csrf_token() .'" autocomplete="off">
					<a href="#"
						class="dropdown-item text-danger delete-popup bs-pass-para show_confirm"
						data-confirm="'. __('Are You Sure?') .'"
						data-text="'. __('This action can not be undone. Do you want to continue?') .'"
						data-confirm-yes="delete-form-'. $estimation->id .'">
						<i class="ti ti-trash"></i>
					</a>
					<input type="hidden" name="_method" value="DELETE">
				</form></div>';
			}
            $action .= '</div>';


            $row                    	= array();
            $row['status']           	= '<span class="badge fix_badges bg-'. $statuesColor[$estimationStatus[$estimation->status]].' p-2 px-3 rounded">'. $estimationStatus[$estimation->status].'</span>';
            $row['project_title']       = '<a href="'. route('project.show',[$estimation->project_id]) .'">'. $estimation->getProjectDetail->name .'</a>';
			$row['estimation_title'] 	= '<a href="'. route('estimations.setup.estimate',\Crypt::encrypt($estimation->id)) .'">'. $estimation->title .'</a>';
            $row['net_inc_discount']  	= currency_format_with_sym((isset($estimation->final_quote->net_with_discount) ? $estimation->final_quote->net_with_discount : 0));
            $row['gross_inc_discount'] 	= currency_format_with_sym((isset($estimation->final_quote->gross_with_discount) ? $estimation->final_quote->gross_with_discount : 0));
			$row['discount'] 			= number_format((isset($estimation->final_quote->discount) ? $estimation->final_quote->discount : 0), 2, ',', '.') . " %";
            $row['issue_date'] 			= date('d.m.y', strtotime($estimation->issue_date));
			$row['action']  			= $action;

            $data[]  = $row;
        }

		$response = array(
            "recordsTotal"          => $total_count,
            "recordsFiltered"       => $filter_count,
            "data"                  => $data,
        );
		
		return $response;
	}

	public function create($project_id = "")
	{
		$user 		= Auth::user();
		if ($user->isAbleTo('estimation create')) {
			$estimation = ProjectEstimation::where('project_id', $project_id)->count();
	
			$title 		= !empty($estimation) ? __("Estimation") . " " . ($estimation + 1) : __("Estimation") . " 1";
			$project 	= Project::find($project_id);
	
			$estimation = new ProjectEstimation();
			$estimation->project_id = $project_id;
			$estimation->title = $title;
			$estimation->issue_date = date('Y-m-d');
			$estimation->status = 1;
			$estimation->created_by = $user->id;
	
			if (isset($project->technical_description) && !empty($project->technical_description)) {
				$estimation->technical_description = $project->technical_description;
			}
			$estimation->save();
	
			$estimation_id = $estimation->id;
	
			$quote = ProjectEstimation::find($estimation_id);
			$company_details = getCompanyAllSetting();
			$quate_title = $company_details['company_name'];
			if ($user->type != "company") {
				$quate_title = $user->name;
			}
	
			$estimate_quote = [
				"title" => $quate_title,
				"net" => $quote->net,
				"net_with_discount" => $quote->net_with_discount,
				"gross" => $quote->gross,
				"gross_with_discount" => $quote->gross_with_discount,
				"discount" => $quote->discount,
				"tax" => $quote->tax,
				"is_clone" => 0,
				"markup" => $quote->markup,
				"project_estimation_id" => $quote->id,
				"project_id" => $project_id,
				'is_final' => 1,
			];
	
			if ($user->type != 'company') {
				$estimate_quote['user_id'] = $user->id;
			}
			EstimateQuote::create($estimate_quote);
	
			return redirect()->route("estimations.setup.estimate", ['id' => Crypt::encrypt($estimation_id)]);
		} else {
			return redirect()->back()->with('error', __('Permission Denied.'));
		}
	}

	public function setup($id)
	{
        
		$user	= Auth::user();
		if ($user->isAbleTo('estimation edit')) {
			$encryptId              = $id;
			$id                     = Crypt::decrypt($id);
			$estimation 			= ProjectEstimation::whereId($id)->with('quotes')->first();
			
			if ($user->type != 'company') {
				$estimation 		= ProjectEstimation::whereId($id)->with('user_quotes')->first();
				if (count($estimation->user_quotes) == 0) {
					return redirect()->back()->with('error', __('Something went wrong.'));
				}
			}
			$estimation_products 	= $estimation->estimation_products()->orderByRaw('position')->get();
	
			$estimation_groups 		= EstimationGroup::where('estimation_id', $estimation->id)->whereNull('parent_id')->with('children_data', 'estimation_products')->orderBy('position')->get();
	
			$ai_description_field   = null;
			$desc_template          = SmartTemplate::where('type', 0)->first();
			if (isset($desc_template->id) && $user->type == 'company') {
				foreach ($estimation_products as $product) {
					if ($product->ai_description != '') {
						$ai_description_field   = true;
						break;
					}
				}
			}
			if (!isset($ai_description_field) && $user->type == 'company') {
				$sp_queues 			= SmartPromptQueue::where('type', 0)->where('estimation_id', $estimation->id)->count();
				if ($sp_queues > 0) {
					$ai_description_field   = true;
				}
			}
	
			$company_details 		= getCompanyAllSetting();
			$company_name 			= $company_details['company_name'];
			$all_contractors 		= genericGetContacts();
	
			if ($user->type == 'company') {
				$projects = Project::where('created_by', '=', $user->id)->get()->pluck('name', 'id');
			} else {
				$projectsModel = Project::leftjoin('client_projects', 'client_projects.project_id', 'projects.id')->leftjoin('estimate_quotes', 'estimate_quotes.project_id', 'projects.id');
				$projectsModel->where(function ($query) use ($user) {
					$query->where('client_projects.client_id', $user->id)
						->orWhere('estimate_quotes.user_id', $user->id);
				});
				$projectsModel->select('projects.*');
				$projectsModel->groupBy('projects.id');
	
				$projects = $projectsModel->get()->pluck('name', 'id');
			}
	
			$project_id = $estimation->project_id;
			
			$total_prices           = [];
			
			$allQuotes              = $estimation->quotes;
			if ($user->type != 'company') {
				$allQuotes     		= $estimation->user_quotes;
			}
			$final_id               = 0;
			$client_final_quote_id  = 0;
			$sub_contractor_final_quote_id = 0;
			$first_quote_id         = 0;
	
			if (isset($allQuotes) && count($allQuotes) > 0) {
				foreach ($allQuotes as $key => $quote) {
					$contractor["sc" . $quote->id]  = $quote->user->name ?? $quote->title;
					$gross['gross_sc' . $quote->id] = $quote->gross;
					$gross_with_discount['gross_with_discount_sc' . $quote->id] = $quote->gross_with_discount;
					$net['net_sc' . $quote->id] = $quote->net;
					$net_with_discount['net_with_discount_sc' . $quote->id] = $quote->net_with_discount;
					$vat['tax_sc' . $quote->id] = $quote->tax;
					$discount['discount_sc' . $quote->id] = currency_format_with_sym($quote->discount,'','',false);
					$markup['markup_sc' . $quote->id] = $quote;
					if ($quote->is_final == 1) {
						$final_id = $quote->id;
					}
					if ($key == 0) {
						$first_quote_id = $quote->id;
					}
					if($quote->final_for_client == 1) {
						$client_final_quote_id  = $quote->id;
					}
					if($quote->final_for_sub_contractor == 1){
						$sub_contractor_final_quote_id  = $quote->id;
					}
				}
	
				$total_prices = [
					'contractors' => $contractor,
					'net_with_discount' => $net_with_discount,
					'gross_with_discount' => $gross_with_discount,
					'net' => $net,
					'gross' => $gross,
					'tax' => $vat,
					'discount' => $discount,
					'markup' => $markup
				];
	
				$smart_templates    = SmartTemplate::get();
	
				$quote_items_ids = array();
				foreach ($estimation_products as $key => $value) {
					$quote_items_ids[] = $value->id;
				}
	
				$quote_items = array();
				$result = EstimateQuoteItem::whereIn('product_id', $quote_items_ids)->with('quote')->orderBy('estimate_quote_id')->get();
				foreach ($result as $row) {
					if ($user->type == "company") {
						if (isset($row->quote->is_display) && $row->quote->is_display == 1) {
							$quote_items[$row->product_id][] = $row;
						}
					} else {
						if (isset($row->quote->user_id) && $row->quote->user_id == $user->id) {
							$quote_items[$row->product_id][] = $row;
						}
					}
				}
				$filters_request['order_by'] = array('field' => 'projects.created_at', 'order' => 'DESC');
				$project_record = Project::get_all($filters_request);
				$all_projects = isset($project_record['records']) ? $project_record['records'] : array();
				$site_money_format  = site_money_format();
	
				return view("taskly::project_estimations.setup", compact('encryptId', 'estimation_products', 'estimation', 'total_prices', 'all_contractors', 'allQuotes', 'final_id', 'company_name', 'first_quote_id', 'projects', 'project_id', 'smart_templates', 'ai_description_field', 'client_final_quote_id', 'sub_contractor_final_quote_id', 'quote_items', 'user', 'estimation_groups', 'all_projects','site_money_format'));
			} else {
				return redirect()->back()->with('error', __('Something went wrong.'));
			}
		} else {
			return redirect()->back()->with('error', __('Permission Denied.'));
		}
	}

	public function ImportEstimationsData(Request $request) {
		$user 		= Auth::user();
		if ($user->isAbleTo('estimation import')) {
			$excelMimes = ['text/xls', 'text/xlsx', 'application/excel', 'application/vnd.msexcel', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','text/csv'];
			$gaebMimes  = ['application/octet-stream'];
			if (is_uploaded_file($_FILES['import_file']['tmp_name']) && !empty($_FILES['import_file']['name'])) {
				$project_estimation = array();
				if (in_array($_FILES['import_file']['type'], $excelMimes)) {
					$project_estimation                 = $this->process_excel_file($request, $_FILES);
				} else if (in_array($_FILES['import_file']['type'], $gaebMimes)) {
					$project_estimation                 = $this->process_gaeb_file($request, $_FILES);
				} else {
					return redirect()->back()->with("Invalid file");
				}
	
				if (! empty($project_estimation)) {
					$table_rows = array();
					foreach($project_estimation['products'][0] as $r_key => $product){
						$table_rows[] = $r_key;
					}
		
					$project_estimation['table_rows']           = $table_rows;
	
					$project_estimation['title']        = $request->title;
					$project_estimation['project_id']   = $request->project;
					$project_estimation['estimation_id']= $request->estimation_id;
					$project_estimation['issue_date']   = $request->issue_date;
	
					return view('taskly::project_estimations.importeddata', compact('project_estimation'));
				}
			} else {
				return redirect()->back()->with("Error");
			}
		} else {
			return redirect()->back()->with('error', __('Permission Denied.'));
		}
    }

	private function process_excel_file(Request $request, $file) {
        $_FILES = $file;

        if($_FILES['import_file']['type'] == "text/csv"){
            $reader = new Csv();
        } else if($_FILES['import_file']['type'] == "text/xls") {
            $reader = new Xls();
        } else {
            $reader = new Xlsx();
        }

        $defaultHeaders = ['quantity', 'unit', 'optional', 'title', 'single_price', 'type', 'description'];
        $spreadsheet    = $reader->load($_FILES['import_file']['tmp_name']);
        $worksheet      = $spreadsheet->getActiveSheet();
        $worksheet_arr  = $worksheet->toArray();

        // Suchen der Kopfzeile
        $headerRowIndex = null;
        foreach ($worksheet_arr as $index => $row) {
            $lowercasedRow = array_map('strtolower', $row);
            $matchedHeaders = 0;

            // Überprüfen der verschiedenen Begriffe
            if (count(array_intersect(['quantity', 'menge', 'qty', 'anzahl'], $lowercasedRow)) > 0) $matchedHeaders++;
            if (count(array_intersect(['unit', 'me', 'einheit', 'mengeneinheit'], $lowercasedRow)) > 0) $matchedHeaders++;
            if (count(array_intersect(['title', 'kurztext', 'titel'], $lowercasedRow)) > 0) $matchedHeaders++;
            if (count(array_intersect(['single_price', 'ep', 'single', 'Einzelpreis'], $lowercasedRow)) > 0) $matchedHeaders++;
            if (count(array_intersect(['Optional', 'Eventualposition', 'EV', 'Optionalposition', 'Optional Position'], $lowercasedRow)) > 0) $matchedHeaders++;
            if (count(array_intersect(['description', 'beschreibung', 'desc', 'langtext'], $lowercasedRow)) > 0) $matchedHeaders++;

            // Überprüfung, ob mindestens 3 Begriffe übereinstimmen
            if ($matchedHeaders >= 3) {
                $headerRowIndex = $index;
                break;
            }
        }

        if ($headerRowIndex === null) {
            // Verwenden Sie Standardüberschriften, wenn keine Kopfzeile gefunden wird
            $heading = $defaultHeaders;
        } else {
            // Verwenden Sie die gefundene Kopfzeile
            // Entfernt zusätzliche Leerzeichen und normalisiert die Groß-/Kleinschreibung
            $heading = array_map(function($header) {
                $header = str_replace(' ', '_', $header);
                return strtolower(trim($header));
            }, $worksheet_arr[$headerRowIndex]);
        }
        $total_single_prices = 0;
		$single_price_headers = array('single_price', 'ep', 'single', 'Einzelpreis');
        foreach($heading as $heading_file) {
			if (in_array($heading_file, $single_price_headers)) {
                $total_single_prices++;
            }
        }

	    $columnMap = [
            'pos'           => $this->findFirstColumn($heading, ['oz', 'pos', 'pos.', 'position', '#', 'Platz', 'Ort', 'Reihenfolge', 'Positionierung', 'Platzierung', 'Sequence', 'Stelle', 'Position Code']),
            'quantity'      => $this->findFirstColumn($heading, ['quantity', 'menge', 'qty', 'Quantität', 'Stückzahl', 'Anzahl', 'Quantity Total', 'Pieces', 'Amount', 'Count']),
            'unit'          => $this->findFirstColumn($heading, ['unit', 'me', 'einheit', 'Einheiten', 'Units', 'U', 'Maßeinheit', 'Measurement', 'Measure', 'Unit Type', 'Metric', 'Base Unit', 'UM', 'Unit of Measure', 'Unit of Issue', 'Measure Type', 'Unit Category', 'Unit Value', 'Unit Label']),
            'title'         => $this->findFirstColumn($heading, ['title', 'kurztext', 'titel', 'name', 'Bezeichnung', 'Label', 'Name Tag', 'Title Text', 'Title Name', 'Text Title', 'Field Name', 'Column Title', 'Title Label']),
            'optional'      => $this->findFirstColumn($heading, ['Optional', 'Eventualposition', 'EV', 'Optionalposition', 'Optional Position', 'Option', 'Voluntary', 'Optional Field', 'Optional Entry', 'Optional Check']),
            'single_price'  => $this->findFirstColumn($heading, ['single price', 'ep', 'single', 'Price', 'Cost', 'Einzelpreis', 'Unit Price', 'Stückpreis', 'Price per Unit', 'Cost per Item', 'Price Each', 'Rate', 'Individual Price', 'Price Single', 'Base Price', 'Rate per Unit', 'Retail Price', 'Wholesale Price', 'List Price', 'Sale Price', 'single_price']),
            'description'   => $this->findFirstColumn($heading, ['beschreibung', 'description', 'desc', 'langtext', 'Details', 'Explanation', 'Text', 'Description Text', 'Description Field', 'Extended Description', 'Full Description']),
            'group_name'    => $this->findFirstColumn($heading, ['group name', 'group', 'gruppe', 'Group Label', 'Category', 'Grouping', 'Section', 'Category Name', 'Group Type', 'Group Tag', 'Gewerk'])
        ];                

        $flipped_column = [];
        foreach($columnMap as $key => $value) {
            if ($value == '') {
                $flipped_column[$key] = $key;
            } else {
                $flipped_column[$value] = $key;
            }
        }
        $previousRow = [];
        $products = array();
        $is_desc_column = 1;
        if(!isset($columnMap['description'])) {
            $is_desc_column = 0;
            $columnMap['description'] = $this->findFirstColumn($heading, ['kurztext']);
        }

        $is_grp_column = 1;
        if(!isset($columnMap['group_name'])) {
            $is_grp_column = 0;
            $columnMap['group_name'] = $this->findFirstColumn($heading, ['kurztext']);
        }

        $groupName          = "";
        $is_mutiple_price   = "No";
        $desc_arr           = array();
        $first_index        = 1;
        $second_index       = 0;
        $last_group_name    = "";
        for ($i = $headerRowIndex + 1; $i < count($worksheet_arr); $i++) {
            $row = $worksheet_arr[$i];
            if (count($heading) != count($row)) {
                continue; // Gehe zur nächsten Zeile, wenn die Anzahl nicht übereinstimmt
            }

            $mappedRow = [];
            foreach($row as $item_key => $item_value) {
                if (isset($flipped_column[$heading[$item_key]])) {
                    //if ($heading[$item_key] == 'ep') {
					if (in_array($heading[$item_key], $single_price_headers)) {
						$mappedRow[$flipped_column[$heading[$item_key]]][] = $item_value;
                    } else {
                        $mappedRow[$flipped_column[$heading[$item_key]]] = $item_value;
                    }
                }
            }
            $qty = (isset($mappedRow['quantity']) && $mappedRow['quantity'] > 0) ? $mappedRow['quantity'] : '';
            $is_single_price = 0;
            if(isset($mappedRow['single_price']) && count($mappedRow['single_price']) > 0){
                foreach($mappedRow['single_price'] as $s_price){
                    if(isset($s_price) && $s_price > 0){
                        $is_single_price = 1;
                    }
                }
            }

            // Prüfen, ob die aktuelle Zeile eine Beschreibung ist
             $isGroupName = !empty($mappedRow['pos']) && empty($qty) && $is_single_price == 0 && (!empty($mappedRow['title']) || !empty($mappedRow['description']) || !empty($mappedRow['group_name']));

            if ($isGroupName) {
                if (isset($mappedRow['group_name'])) {
                    $previousRow['group_name'] = $mappedRow['group_name'];
                } else if (isset($mappedRow['title'])) {
                    $previousRow['group_name'] = $mappedRow['title'];
                } else if (isset($mappedRow['description'])) {
                    $previousRow['group_name'] = $mappedRow['description'];
                }
                continue;
            } if (isset($mappedRow['group_name'])) {
                $groupName = $mappedRow['group_name'];
            }

            $isDescription = empty($mappedRow['pos']) && empty($qty) && $is_single_price == 0 && !empty($mappedRow['title']);
            
            if ($isDescription) {
                $desc['description'] = $mappedRow['title'];
                $desc['key'] = $i - 1;
                $desc_arr[] = $desc;
                continue;
            }

            if (!empty($previousRow)) {
                $mappedRow = array_merge($mappedRow, $previousRow);
                if(isset($previousRow['group_name'])){
                    $groupName = $previousRow['group_name'];
                }
                $previousRow = [];
            }

            // Prüfen, ob die Zeile relevante Daten enthält
            if (empty($mappedRow['quantity']) && empty($mappedRow['single_price']) && empty($mappedRow['title'])) {
                continue; // Überspringt die Verarbeitung dieser Zeile
            }

            $qty = isset($mappedRow['quantity']) ? $this->convertToNumber($mappedRow['quantity']) : 0;
            // $single_price = isset($mappedRow['single_price']) ? $this->convertToNumber($mappedRow['single_price']) : 0;
            $single_price = isset($mappedRow['single_price']) ? $mappedRow['single_price'] : array();
            $new_single_price = array();
            if(count($single_price) > 0){
                foreach($single_price as $s_price) {
                    $new_single_price[] = isset($s_price) ? $this->convertToNumber($s_price) : 0;
                }
            }

            $pos        = $mappedRow['pos'] ?? "";
            $unit       = $mappedRow['unit'] ?? "";
            $optional   = $mappedRow['optional'] ?? 1;
            $title      = $mappedRow['title'] ?? "";

            $description = isset($mappedRow['description']) ? $mappedRow['description'] : '';

            if ($last_group_name == '') {
                $last_group_name = $groupName;
            }
            if ($groupName != $last_group_name) {
                $last_group_name = $groupName;
                $second_index = 0;
                $first_index++;
            } 
            $second_index++;

            if ($pos == '') {
                $pos = $first_index.'.'.$second_index;
            }

            $item['pos']            = $pos;
            $item['groupName']      = $groupName;
            $item['name']           = $title;
            $item['description']    = $description;
            $item['unit']           = $unit;
            $item['optional']       = $optional;
            $item['quantity']       = $qty;
            if($total_single_prices == 1) {
                $item['price']          = $new_single_price[0];
            } else if($total_single_prices > 1) {
                if(count($new_single_price) > 0){
                    $price_number = 1;
                    foreach($new_single_price as $ns_price) {
                            $price_key = "price_" . $price_number;
                            $item[$price_key] = $ns_price;
                            $price_number++;
                            $is_mutiple_price = "Yes";
                    }
                }
            }
            $item['key']            = $i;

            if (($title != "" || $description != '') && ($qty != "" || ! empty($new_single_price))) {
                if ($title == '') {
                    $item['name'] = substr($description, 0, 50);
                }
                
                $products[]         = $item;
            }
        }

        $rows = array();
        $new_rows = array();
        $latest_products = array();
        if(count($products) > 0){
            foreach($products as $r_key => $prod){
                $key = $prod['key'];
                if(count($desc_arr) > 0){
                    foreach($desc_arr as $dsc){
                        if($dsc['key'] == $key) {
                            $prod['description'] = $dsc['description'];
                        }
                    }
                }
                unset($prod['key']);
                $latest_products[] = array_map('utf8_encode',$prod);
            }
        }

        if(count($latest_products) > 0){
            $project_estimation['products']             = $latest_products;
            $project_estimation['total_single_prices']  = $total_single_prices;
        }

        return $project_estimation;
    }

	private function process_gaeb_file(Request $request, $file) {
        $xml_string = file_get_contents($_FILES['import_file']['tmp_name']);
        $xml        = simplexml_load_string($xml_string);
        $json       = json_encode($xml);
        $xml_data   = json_decode($json,TRUE);

        if (isset($xml_data['Award']['BoQ']) && !empty($xml_data['Award']['BoQ'])) {
            while($this->keey_checking) {
                // dd($xml_data['Award']['BoQ']);
                $this->get_items($xml_data['Award']['BoQ']);
            }
        }

        $estimation_item    = array();
        $first_index        = 1;
        $last_group_name    = "";

        // dd($this->parent_pos);
        // dd($this->items_array);

        foreach($this->items_array as $i => $items) {
            $this->get_text($items['group_name'], "span");
            $group_name         = $this->group_name;
            $this->group_name   = array();
            $second_index       = 0;
            $group_pos          = implode(".", array_map(function($key){
                return str_pad($key, 2, '0', STR_PAD_LEFT);
            }, $items['pos']));

            foreach($items as $j => $item) {

                if (isset($item['QU'])) {
                    $pos = "";
                    if (isset($item['@attributes']['RNoPart'])) {
                        $pos = $group_pos.'.'.str_pad($item['@attributes']['RNoPart'], 2, '0', STR_PAD_LEFT);
                    }

                    $this->get_text($item, "TextOutlTxt");
                    $item_name = $this->item_name;
    
                    $this->item_string  = "";
                    $this->get_array_string($item_name);
    
                    $item_name          = $this->item_string;
                    $this->item_name    = array();
    
                    $this->get_text($item, "Text");
                    $item_description       = $this->item_description;

                    $this->item_string      = "";
                    $this->get_array_string($item_description);
    
                    $item_description       = $this->item_string;
                    $this->item_description = array();
    
                    $is_optional    = 1;
                    if (isset($item['Provis']) && ($item['Provis'] == 'WithoutTotal')) {
                        $is_optional    = 0;    
                    }

                    if ($pos == "") {
                        if ($last_group_name == '') {
                            $last_group_name = $group_name;
                        }
                        if ($group_name != $last_group_name) {
                            $last_group_name = $group_name;
                            $second_index = 0;
                            $first_index++;
                        } 
                        $second_index++;

                        $pos = $first_index.'.'.$second_index;
                    }

                    $estimation_item[] = array_map('utf8_encode', array(
                        "pos"           => $pos,
                        "group_pos"     => $group_pos,
                        "groupName"     => $group_name,
                        "name"          => $item_name,
                        "description"   => $item_description,
                        "unit"          => $item['QU'],
                        "optional"      => $is_optional,
                        "quantity"      => $item['Qty'],
                        "price"         => 0
                    ));
                }
            }
        }

        $project_estimation['products']             = $estimation_item;
        $project_estimation['total_single_prices']  = 1;

        return $project_estimation;
    }

	private function get_items($nodes = array()) {
        // dd($nodes);
        foreach($nodes as $key => $node) {
            if ($key == "Itemlist") {
                // dd($this->item_pos[$this->parent_key]);
                $node['Item']['group_name'] = $this->parent_node[$this->parent_key];
                $node['Item']['pos']        = $this->item_pos[0];

                array_pop($this->item_pos[0]);

                $this->items_array[] = $node['Item'];
                $this->parent_key++;
            } else {
                if (is_array($node)) {
                    if (isset($nodes[$key]['RNoPart'])) {
                        $this->item_pos[0][] = $nodes[$key]['RNoPart'];
                    }
                    if (isset($nodes[$key]['LblTx'])) {
                        $this->parent_node[$this->parent_key] = $nodes[$key]['LblTx'];
                    }
                    $this->get_items($node);
                }
            }
        }

        $this->keey_checking = false;
    }

	private function get_text($nodes = array(), $find_key = "") {
        foreach($nodes as $key => $node) {
            if ($key == $find_key) {
                if ($find_key == "TextOutlTxt") {
                    $this->item_name = $node;
                } else if ($find_key == "Text") {
                    $this->item_description = $node;
                } else if ($find_key == "span") {
                    $this->group_name = $node;
                }
            } else if (is_array($node)) {
                $this->get_text($node, $find_key);
            }
        }
    }

	private function get_array_string($items = array()) {
        foreach($items as $key => $item) {
            if (is_array($item)) {
                $this->get_array_string($item);
            } else {
                if (!in_array($key, array('@attributes', 'style', 'image'))){
                    $this->item_string .= trim($item); 
                }
            }
        }
    }

	// Neue Funktion, um die erste übereinstimmende Spalte zu finden
	private function findFirstColumn($heading, $possibleColumns)
	{
		foreach ($heading as $colName) {
			if (in_array(strtolower($colName), array_map('strtolower', $possibleColumns))) {
				return $colName;
			}
		}
		return null;
	}

	// Funktion, um Zahlenwerte zu bereinigen und zu konvertieren
	private function convertToNumber($value)
	{
		$number = 0;
		if (is_numeric($value)) {
			$number = (float)$value;
		} else {
			// Versucht, nicht-numerische Werte als Zahlen zu interpretieren
			$cleanedValue = preg_replace('/[^0-9\.\,]+/', '', $value);
			// Ersetzt Kommas durch Punkte für die Konvertierung
			$cleanedValue = str_replace(',', '', $cleanedValue);
		//  $cleanedValue = floatval($cleanedValue);

		//  $cleanedValue = convert_to_site_money_format($cleanedValue, true, $this->settings['site_money_format']);
			$number = is_numeric($cleanedValue) ? (float)$cleanedValue : 0;
		}
		return $number;
	}

	public function StoreImportEstimations(Request $request) {
        $table_data = isset($request->table_data) ? json_decode($request->table_data) : array();

        if (count($table_data) > 0) {
            $selected_row = $request->selected_row;
            $total_single_prices = $request->total_single_prices;
            $title = $request->title;
            if (empty($title)) {
                $estimation = ProjectEstimation::orderBy("id", "desc")->first();
                $title = "{{ __('Estimation') }} " . ($estimation->id + 1);
            }
            $multiple_prices = array();
            if($total_single_prices > 1) {
                foreach($table_data as $product_data) {
                    $my_products1 = array();
                    $price = "";
                    foreach($product_data as $value1) {
                        $my_products1[] = $value1;
                    }
                    if(count($selected_row) > 0) {
                        foreach($selected_row as $k1 => $row1){
                            $field = isset($my_products1[$k1]) ? $my_products1[$k1] : '';
                            if (str_contains($row1, 'price_')) {
                                $multiple_prices[$row1][] = $field;
                            }
                        }
                    }
                }
            }

            $estimation = ProjectEstimation::find($request->estimation_id);
            $estimation->project_id = $request->project_id;
            $estimation->title = $title;
            $estimation->issue_date = $request->issue_date;
            $estimation->status = 1;
            // $estimation->created_by = Auth::user()->id;
            // $estimation->init_status = 1;
            $estimation->save();
            $estimation_id = $estimation->id;
            $net_total = 0;
            ProjectEstimationProduct::where('project_estimation_id', $request->estimation_id)->delete();
            EstimateQuote::where('project_estimation_id', $request->estimation_id)->delete();

			$last_group_pos  = "";
			$last_group_id  = "";
			$last_group_name    = "";
			$group_position  = 0;
			$item_prices = array();
            foreach($table_data as $product) {
                $name = "";
                $description = "";
                $unit = "";
                $quantity = "";
                $price = "";
                $pos = "";
                $group_name = "";
                $optional = 0;

                $my_products = array();
                foreach($product as $k => $value) {
                    $my_products[] = utf8_decode($value);
                }

                if(count($selected_row) > 0) {
                    foreach($selected_row as $k => $row){
                        $field = isset($my_products[$k]) ? $my_products[$k] : '';
                        if ($row == "name") {
                            $name = $field;
                        } else if ($row == "description") {
                            $description = $field;
                        } else if ($row == "optional") {
                            $optional = ($field == 0) ? 0 : 1;
                        } else if ($row == "unit") {
                            $unit = $field;
                        } else if ($row == "quantity") {
                            $quantity = $field;
                        } else if ($total_single_prices == 1 && $row == "price") {
                            $price = $field;
                        } else if ($total_single_prices > 1 && $row == "price_1") {
                            $price = $field;
                        } else if ($row == "pos") {
                            // $pos = $this->formatNumber($field);
                            $pos = $field;
                        } else if ($row == "groupName") {
                            $group_name = $field;
                        }
                    }
                }
                $total_price = ($optional == 0) ? 0 : round($quantity * $price, 2);
				$net_total += $total_price;

				if ($group_name != $last_group_name) {
					$last_group_name = $group_name;
					$group_position++;

					$est_grp_data = array();
					$est_grp_data['group_pos'] = str_pad($group_position, 2, 0, STR_PAD_LEFT);
					$est_grp_data['group_name'] = $group_name;
					$est_grp_data['estimation_id'] = $estimation_id;
					$est_grp_data['position'] = $group_position;

					$new_group = EstimationGroup::create($est_grp_data);
					$last_group_id  = $new_group->id;
				} 
				
                $item = new ProjectEstimationProduct();
                $item->project_estimation_id = $estimation_id;
				$item->group_id = $last_group_id;
                $item->pos = $pos;
                $item->type = "item";
                $item->name = $name;
                $item->is_optional = $optional;
                $item->description = $description;
                $item->unit = $unit;
                $item->quantity = $quantity;
                $item->save();

				$item_id = $item->id;
				$item_data['price'] = round($price, 2);
				$item_data['total_price'] = $total_price;
				$item_prices[$item_id] = $item_data;
            }
        

            $quote = ProjectEstimation::find($estimation_id);;
            $quote->quoteItem = $quote->estimation_products;
            $quote->project_estimation_id = $quote->id;

			$company_details	= getCompanyAllSetting();
			$user 			= Auth::user();
			$quate_title 	= $company_details['company_name'];
			if($user->type != "company") {
				$quate_title = $user->name;
			}
            $user_id = null;
            $user_data = User::find($quote->created_by);
            if (!empty($user_data)) {
                if ($user_data->type != "company") {
                    $user_id = ($quote->created_by) ? $quote->created_by : null;
                } else {
                    if ($user->type != "company") {
                        $user_id = $user->id;
                    }
                }
            }

            if ($total_single_prices == 1) {
                $new_quote = EstimateQuote::create([
                    "title" => $quate_title,
                    "net" => $net_total,
                    "net_with_discount" => $net_total,
                    "gross" => $net_total,
                    "gross_with_discount" => $net_total,
                    "discount" => 0,
                    "tax" => 0,
                    "is_clone" => 0,
                    "markup" => 0,
                    "project_estimation_id" => $quote->project_estimation_id,
                    "project_id" => $quote->project_id,
                    'is_final' => 1,
                    'user_id' => $user_id,
                ]);
                foreach ($quote->quoteItem as $item) {
                    EstimateQuoteItem::create([
                        "estimate_quote_id" => $new_quote->id,
                        "product_id" => $item->id,
                        "price" => $item_prices[$item->id]['price'],
                        "base_price" => $item_prices[$item->id]['price'],
                        "total_price" => $item_prices[$item->id]['total_price'],
                    ]);
                }
            } else if($total_single_prices > 1) {
                for ($x = 1; $x <= $total_single_prices; $x++) {
                    $quote_net_total = 0;
                    $quote_title = $quate_title;
                    if($x > 1){
                        $quote_title = $title;
                    }
                    $is_final = 0;
                    if($x == $total_single_prices){
                        $is_final = 1;
                    }
                    $new_quote = EstimateQuote::create([
                        "title" => $quote_title,
                        "net" => $quote_net_total,
                        "is_clone" => 0,
                        "project_estimation_id" => $quote->project_estimation_id,
                        "project_id" => $quote->project_id,
                        'is_final' => $is_final,
                        'user_id' => $user_id,
                    ]);
                    foreach ($quote->quoteItem as $q_key => $item) {
                        $prc_field = "price_".$x;
                        $q_price = 0;
                        if(count($multiple_prices) > 0){
                            $q_price = isset($multiple_prices[$prc_field][$q_key]) ? $multiple_prices[$prc_field][$q_key] : 0;
                        }

                        $price              = round($q_price,2);
                        $qty                = round($item->quantity,2);
                        $total_price        = ($item->is_optional == 0) ? 0 : round($qty * $price, 2);
                        $quote_net_total    += $total_price;

                        EstimateQuoteItem::create([
                            "estimate_quote_id" => $new_quote->id,
                            "product_id" => $item->id,
                            "price" => $price,
                            "base_price" => $price,
                            "total_price" => $total_price,
                        ]);
                    }

                    $estimate_Quote = EstimateQuote::find($new_quote->id);
                    $estimate_Quote->net = $quote_net_total;
                    $estimate_Quote->gross = $quote_net_total;
                    $estimate_Quote->net_with_discount = $quote_net_total;
                    $estimate_Quote->gross_with_discount = $quote_net_total;
                    $estimate_Quote->save();
                }
            }
    
            //return redirect()->route("estimations.edit.estimate", ['id' => Crypt::encrypt($estimation_id)]);
            return redirect()->route("estimations.setup.estimate", ['id' => Crypt::encrypt($estimation_id)]);
        } else {
            return redirect()->back()->with("Error");
        }
    }

	public function copyEstimation($estimation_id)
    {
		$user 		= Auth::user();
		if ($user->isAbleTo('estimation copy')) {
			return view('taskly::project_estimations.copy', compact('estimation_id'));
		} else {
			return response()->json(['error' => __('Permission denied.')], 401);
		}
    }

	public function copyEstimationStore(Request $request, $estimation_id)
    {
		$user = Auth::user();
        $estimation = ProjectEstimation::find($estimation_id);
        $new_estimation = ProjectEstimation::create([
            'title' => $estimation->title . " - Copy",
            'project_id' => $estimation->project_id,
            'issue_date' => $estimation->issue_date,
			'technical_description' => $estimation->technical_description,
            'created_by' => $estimation->created_by,
            'status' => 0,
        ]);

        $new_product_ids = array();
		$last_group_id = "";
		foreach ($estimation->estimation_groups()->orderBy('group_pos')->get() as $key => $item_group){
			$est_grp_data 					= array();
			$est_grp_data['group_pos'] 		= $item_group->group_pos;
			$est_grp_data['group_name'] 	= $item_group->group_name;
			$est_grp_data['estimation_id'] 	= $new_estimation->id;
			$est_grp_data['position'] 		= $item_group->position;
			if(isset($item_group->parent_id) && !empty($last_group_id)) {
				$est_grp_data['parent_id'] 		= $last_group_id;
			}

			$new_group 		= EstimationGroup::create($est_grp_data);
			$last_group_id = $new_group->id;
			foreach ($item_group->estimation_products as $product) {
				$new_product = ProjectEstimationProduct::create([
					'project_estimation_id' => $new_estimation->id,
					'group_id' => $new_group->id,
					'type' => $product->type,
					'name' => $product->name,
					'description' => $product->description,
					'pos' => $product->pos,
					'unit' => $product->unit,
					'quantity' => $product->quantity,
					'is_optional' => $product->is_optional,
					'comment' => $product->comment,
					'campare_percent' => $product->campare_percent,
				]);
	
				if (isset($new_product->id)) {
					$new_product_ids[$product->id] = $new_product->id;
				}
			}
        }

        if ($request->has('quotes') && $request->quotes) {
            foreach ($estimation->quotes as $quote) {
                $new_quote = EstimateQuote::create([
                    'title' => $quote->title,
                    'sub_contractor_id' => $quote->sub_contractor_id,
                    'project_estimation_id' => $new_estimation->id,
                    'project_id' => $new_estimation->project_id,
                    'tax' => $quote->tax,
                    'discount' => $quote->discount,
                    'net' => $quote->net,
                    'gross' => $quote->gross,
                    'net_with_discount' => $quote->net_with_discount,
                    'gross_with_discount' => $quote->gross_with_discount,
                    'is_clone' => $quote->is_clone,
                    'markup' => $quote->markup,
                    'is_final' => $quote->is_final,
                ]);

                foreach ($quote->quoteItem as $item) {
                    EstimateQuoteItem::create([
                        'estimate_quote_id' => $new_quote->id,
                        'product_id' => $new_product_ids[$item->product_id],
                        'price' => $item->price,
                        'base_price' => $item->price,
                        'total_price' => $item->total_price,
                    ]);
                }

            }
        } else {
            $quote = ProjectEstimation::with("estimation_products")->find($new_estimation->id);
            $quote->quoteItem = $quote->estimation_products;
            $quote->project_estimation_id = $quote->id;

			$company_details	= getCompanyAllSetting();
			$quate_title 		= $company_details['company_name'];
			if($user->type != "company") {
				$quate_title = $user->name;
			}
    
            $new_quote = EstimateQuote::create([
                "title" => $quate_title,
                "net" => $quote->net + ($quote->net * $quote->markup / 100),
                "net_with_discount" => $quote->net_with_discount + ($quote->net_with_discount * $quote->markup / 100),
                "gross" => $quote->gross + ($quote->gross * $quote->markup / 100),
                "gross_with_discount" => $quote->gross_with_discount + ($quote->gross_with_discount * $quote->markup / 100),
                "discount" => $quote->discount,
                "tax" => $quote->tax,
                "is_clone" => 0,
                "markup" => $quote->markup,
                "project_estimation_id" => $quote->project_estimation_id,
                "project_id" => $quote->project_id,
                'is_final' => 1,
            ]);
            foreach ($quote->quoteItem as $item) {
                EstimateQuoteItem::create([
                    "estimate_quote_id" => $new_quote->id,
                    "product_id" => $item->id,
                    "price" => $item->price,
                    "base_price" => $item->price,
                    "total_price" => $item->total_price,
                ]);
            }
        }
        return redirect()->back()->with('success', __('Estimation Copy Successfully.'));
    }

	public function deleteProjectEstimation($id)
    {
		$user 		= Auth::user();
		if ($user->isAbleTo('estimation delete')) {
			ProjectEstimation::find($id)->delete();
			return redirect()->back()->with('success', __('Estimation Deleted Successfully.'));
		} else {
			return redirect()->back()->with('error', __('Permission Denied.'));
		}
    }

	public function estimationAllowedUsers($estimationId)
    {
		$user 		= Auth::user();
		if ($user->isAbleTo('estimation invite user')) {
			$users = genericGetContacts();
			return view('taskly::project_estimations.userAdd', compact('estimationId','users'));
		} else {
			return response()->json(['error' => __('Permission denied.')], 401);
		}
    }

	public function storeUsers(Request $request, $estimation_id)
    {
        if (Auth::user()->type == 'company') {
			$validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required',
                ]
            );
			$estimation = ProjectEstimation::find($estimation_id);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
			$invited = 0;
            foreach ($request->user_id as $key => $selected_user) {
                $user = User::findOrFail($selected_user);
				$exists_contact = EstimateQuote::where('project_id',$estimation->project_id)->where('project_estimation_id', $estimation_id)->where('user_id', $selected_user)->first();
				if(isset($exists_contact->id)) {
					continue;
				}

				$invited++;

				$quote = ProjectEstimation::with("estimation_products")->find($estimation_id);
            	$quote->quoteItem = $quote->estimation_products;
            	$quote->project_estimation_id = $quote->id;

				$quote_title = 'Neuwest';
				if(isset($selected_user)){
					$user_data = User::find($selected_user);
					if(isset($user_data->id)){
						$quote_title = $user_data->name;
					}
				}

                $new_quote = EstimateQuote::create([
                    "title" => $quote_title,
					'user_id' => $selected_user,
                    "is_clone" => 0,
                    "project_estimation_id" => $quote->project_estimation_id,
                    "project_id" => $quote->project_id,
                    'is_display' => 0,
                ]);
                foreach ($quote->quoteItem as $item) {
                    EstimateQuoteItem::create([
                        "estimate_quote_id" => $new_quote->id,
                        "product_id" => $item->id,
                    ]);
                }
				if(isset($user->email)) {
					$this->sendInvitation($user, $estimation_id);
				}
            }
			if($invited > 0) {
				return redirect()->back()->with('success', __('User successfully Invited.'));
			} else {
				return redirect()->back()->with('error', __('User Already invited.'));
			}
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

	public function sendInvitation($user, $estimationIds)
    {
        $estimationIds = [$estimationIds];
        $estimations = ProjectEstimation::whereIn("id", $estimationIds)->get();
        $project_id = $estimations[0]->project_id;
        $userEmail = $user->email;
        $content = (object) [
            'name' => isset($user->name) ? $user->name : '',
            'estimations' => $estimations
        ];
        $message = "<h4>Liebling" . $user->name . ",</h4>";
        $message .= "<p style='margin-left: 110px'>Sie sind zu folgenden Kostenvoranschlägen eingeladen:</p>";
        $message .= "Project: <a href='" . route('projects.show', [$estimations[0]->project_id]) . "'>" . $estimations[0]->project()->title . "</a><br>";
        $message .= "<ol>";
        foreach ($estimations as $estimation) {
            $message .= "<li><a href='" . route("estimations.setup.estimate", [encrypt($estimation->id)]) . "'>" . $estimation->title . "</a></li>";
        }
        $message .= "</ol>";
		$html_data = view('taskly::email.invitation_email', compact('content'))->render();
        $emailData = (object) [
            "subject" => "Invitation",
            "sender_name" => env("APP_NAME"),
            "content" => $html_data,
            "sender" => env("MAIL_FROM_ADDRESS"),
            "view" => 'taskly::email.common'
        ];
        $email = Email::create([
            'subject' => "Invitation",
            "message" => $message,
            "status" => 1,
            "project_id" => $project_id,
            "estimations" => json_encode($estimationIds)
        ]);
        $sender = User::find(Auth::user()->id);
        $recipient = User::find($user->id);
        $sender->sentEmails()->save($email);
        $recipient->receivedEmails()->save($email);
		$setconfing =  SetConfigEmail();
		$smtp_error = [];
        $smtp_error['status'] = true;
        $smtp_error['msg'] = '';
		if ($setconfing ==  true) {
			try {
				Mail::to($userEmail)->send(new CommonEmailTemplate($emailData));
			} catch (\Exception $e) {
				$smtp_error['status'] = false;
				$smtp_error['msg'] = $e->getMessage();
			}
		} else {
			$smtp_error['status'] = false;
			$smtp_error['msg'] = __('Something went wrong please try again ');
		}
		return $smtp_error;
    }

	public function updateEstimationPos(Request $request){
        if (isset($request->estimation_id) && !empty($request->estimation_id)) {
            $estimation_details = '';
            $estimation_model = new ProjectEstimation();
            $estimation_details = $estimation_model->reorderEstimationPos($request->estimation_id);
            if (!empty($estimation_details)) {
                if (isset($estimation_details['status']) && $estimation_details['status'] == true) {
                    return response(['status' => true, 'message' => __('Pos updated successfully.'), 'data' => $estimation_details['data']]);
                } else {
                    return response(['status' => false, 'message' => __('Something went wrong.')]);
                }
            } else {
                return response(['status' => false, 'message' => __('Something went wrong.')]);
            }
        } else {
            return response(['status' => false, 'message' => __('Estimation id not found.')]);
        }
    }

	public function add_item(Request $request)
    {
        try {
			$user 		= Auth::user();
			if (isset($request->item_with_group) && $request->item_with_group == true) {
				if (!$user->isAbleTo('estimation add group option')) {
					return response()->json(['status' => false, 'message' => __('Permission Denied.')]);
				}
			}
			if ($user->isAbleTo('estimation add item option')) {
				if (!empty($request->estimation_id)) {
					$project_estimation 	= ProjectEstimation::select('id', 'init_status', 'created_at')
														->with(['all_quotes:id,markup,project_estimation_id'])
														->find($request->estimation_id);
					if(isset($project_estimation->id)) {
	
						$groupedProducts 	= EstimationGroup::select('id', 'estimation_id', 'group_name', 'group_pos', 'position')->where('estimation_id', $project_estimation->id)->orderBy('group_pos')->get();
	
						$total_groups 		= $groupedProducts->pluck('id')->toArray();
	
						$last_group 		= end($total_groups);
						$estimationGroup 	= EstimationGroup::find($last_group);
						$group_pos   		= 1;
						$group_pos    		= str_pad($group_pos, 2, '0', STR_PAD_LEFT);
						if(isset($estimationGroup->id)) {
							$group_pos   	= $estimationGroup->group_pos;
						}
	
						$total_items_groups = ProjectEstimationProduct::where('group_id', $last_group)->pluck('id')->toArray();
						$total_items_count 	= count($total_items_groups);
	
	
						$item_number    	= $total_items_count +1;
						$item_number    	= str_pad($item_number, 2, '0', STR_PAD_LEFT);
						
						$pos 				= $group_pos.'.'.$item_number;
	
						$with_group = false;
						if(isset($request->item_with_group) && $request->item_with_group == true) {
							$last_estimationGroup 	= EstimationGroup::where('estimation_id', $project_estimation->id)->whereNull('parent_id')->orderBy('group_pos','DESC')->first();
							if(isset($last_estimationGroup->id)) {
								$new_grp_position 	= $last_estimationGroup->position + 1;
								$new_group_number  	= $last_estimationGroup->group_pos + 1;
								$new_group_number	= str_pad($new_group_number, 2, '0', STR_PAD_LEFT);
	
								$est_grp_data 					= array();
								$est_grp_data['group_pos'] 		= $new_group_number;
								$est_grp_data['group_name'] 	= "Group";
								$est_grp_data['estimation_id'] 	= $project_estimation->id;
								$est_grp_data['position'] 		= $new_grp_position;
	
								$new_group 		= EstimationGroup::create($est_grp_data);
								$last_group  	= $new_group->id;
								$number         = 1;
								$number         = str_pad($number, 2, '0', STR_PAD_LEFT);
								$pos            = $new_group_number.'.'.$number;
								$with_group 	= true;
							}
						}
						if(count($total_groups) == 0) {
							$new_grp_position 	= 1;
							$new_group_number	= str_pad($new_grp_position, 2, '0', STR_PAD_LEFT);
	
							$est_grp_data 					= array();
							$est_grp_data['group_pos'] 		= $new_group_number;
							$est_grp_data['group_name'] 	= "Group";
							$est_grp_data['estimation_id'] 	= $project_estimation->id;
							$est_grp_data['position'] 		= $new_grp_position;
	
							$new_group 		= EstimationGroup::create($est_grp_data);
							$last_group  	= $new_group->id;
							$number         = 1;
							$number         = str_pad($number, 2, '0', STR_PAD_LEFT);
							$pos            = $new_group_number.'.'.$number;
							$with_group 	= true;
						}
						$newProjectEstimationProduct = ProjectEstimationProduct::create([
							"project_estimation_id" => $project_estimation->id,
							'group_id' => $last_group,
							'type' => 'item',
							'pos' => $pos,
							'is_optional' => 1,
						]);
						$quote = ProjectEstimation::with("estimation_products")->find($project_estimation->id);
						$queues_result = $quote->getQueuesProgress();
						$quote->quoteItem = $quote->estimation_products;
						$estimation_quotes = $project_estimation->all_quotes;
						
						if(count($estimation_quotes) > 0) {
							$insert_array = array();
							$filteredColumnArray = [];
							$estimation_quote_ids = array();
							foreach($estimation_quotes as $estimation_quote) {
								if(isset($estimation_quote->id)) {
									$old_qoute_items = EstimateQuoteItem::select('price','base_price','all_results','smart_template_data')->where('estimate_quote_id', $estimation_quote->id)->get();
									$old_quote_array = array();
									if(count($old_qoute_items) > 0) {
										foreach($old_qoute_items as $old_qoute_item) {
											$old_quate_prices['price'] = $old_qoute_item->price;
											$old_quate_prices['base_price'] = $old_qoute_item->base_price;
											$old_quate_prices['all_results'] = $old_qoute_item->all_results;
											$old_quate_prices['smart_template_data'] = $old_qoute_item->smart_template_data;
											$old_quote_array[] = $old_quate_prices;
										}
									}
									$estimation_quote_ids[] = $estimation_quote->id;
									   foreach ($quote->quoteItem as $qi_key => $item) {
										$item_price = isset($old_quote_array[$qi_key]['price']) ? $old_quote_array[$qi_key]['price'] : 0;
										$base_price = isset($old_quote_array[$qi_key]['base_price']) ? $old_quote_array[$qi_key]['base_price'] : 0;
										$total_price = round($item->quantity * $item_price, 2);
										$smart_template_data = isset($old_quote_array[$qi_key]['smart_template_data']) ? $old_quote_array[$qi_key]['smart_template_data'] : NULL;
										$all_results = isset($old_quote_array[$qi_key]['all_results']) ? $old_quote_array[$qi_key]['all_results'] : NULL;
										$insert_item = array(
											"estimate_quote_id" => $estimation_quote->id,
											"product_id" => $item->id,
											"base_price" => $base_price,
											"price" => $item_price,
											"total_price" => $total_price,
											"all_results" => $all_results,
											"smart_template_data" => $smart_template_data,
											"created_at" => date('Y-m-d h:i:s'),
											"updated_at" => date('Y-m-d h:i:s')
											
										);
										$insert_array[]         = $insert_item;
	
										if ($item->id === $newProjectEstimationProduct->id) {
											$extraColumn = [
												"estimate_quote_id" => $estimation_quote->id,
												"product_id" => $item->id,
												"unit" => $item->unit,
												"base_price" => $base_price,
												"price" => $item_price,
												"total_price" => $total_price,
											];
	
											$filteredColumnArray[] = $extraColumn;   
										}
									}
								}
							}
							if (count($estimation_quote_ids) > 0) {
								EstimateQuoteItem::whereIn("estimate_quote_id", $estimation_quote_ids)->delete();
							}
							if (count($insert_array) > 0) {
								EstimateQuoteItem::insert($insert_array);
							}
						}
						// $estimation = ProjectEstimation::find($request->estimation_id);
						// $project_estimation->init_status = 1;
						if ($project_estimation->isDirty()) {                    
							$project_estimation->update();
						}
						if(isset($request->item_html)) {
							$product = $newProjectEstimationProduct;
	
							$quote_items_ids = array();
							foreach ($quote->quoteItem as $key => $value) {
								$quote_items_ids[] = $value->id;
							}
							$quote_items = array();
							$result = EstimateQuoteItem::whereIn('product_id', $quote_items_ids)->with('quote')->orderBy('estimate_quote_id')->get();
							foreach($result as $row) {
								if($user->type == "company") {
									if(isset($row->quote->is_display) && $row->quote->is_display == 1) {
										$quote_items[$row->product_id][] = $row;
									}
								} else {
									if(isset($row->quote->user_id) && $row->quote->user_id == $user->id) {
										$quote_items[$row->product_id][] = $row;
									}
								}
							}
							$ai_description_field   = null;
							if(isset($request->ai_description_field) && $request->ai_description_field == 1){
								$ai_description_field   = true;        
							}
	
							$html_data = view('taskly::project_estimations.item_row', compact('product','quote_items','with_group','ai_description_field','queues_result'))->render();
	
							return response(['status' => true, 'message' => "Added", 'html_data' => $html_data]);
						} else {
							return response(['status' => true, 'message' => "Added", 'latest_project_estimation' => $newProjectEstimationProduct, 'insert_array' => $filteredColumnArray ?? [] ]);
						}
					} else {
						return response(['status' => false, 'message' => "Estimation not found"]);
					}
				} else {
					return response(['status' => false, 'message' => "Estimation id Required"]);
				}
			} else {
				return response()->json(['status' => false, 'message' => __('Permission Denied.')]);
			}
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
    }

	public function add_comment(Request $request)
    {
		try {
			$user	= Auth::user();
			if ($user->isAbleTo('estimation add comment option')) {
           		if (!empty($request->estimation_id)) {
					$project_estimation = ProjectEstimation::select('id', 'init_status', 'created_at')
														->with(['all_quotes:id,markup,project_estimation_id'])
														->find($request->estimation_id);
					if(isset($project_estimation->id)) {
	
						$groupedProducts 	= EstimationGroup::select('id', 'estimation_id', 'group_name', 'group_pos', 'position')->where('estimation_id', $project_estimation->id)->orderBy('group_pos')->get();
	
						$total_groups 		= $groupedProducts->pluck('id')->toArray();
	
						$last_group 		= end($total_groups);
						$estimationGroup 	= EstimationGroup::find($last_group);
						$group_pos   		= 1;
						$group_pos    		= str_pad($group_pos, 2, '0', STR_PAD_LEFT);
						if(isset($estimationGroup->id)) {
							$group_pos   	= $estimationGroup->group_pos;
						}
	
						$total_items_groups = ProjectEstimationProduct::where('group_id', $last_group)->pluck('id')->toArray();
						$total_items_count 	= count($total_items_groups);
	
	
						$item_number    	= $total_items_count +1;
						$item_number    	= str_pad($item_number, 2, '0', STR_PAD_LEFT);
						
						$pos 				= $group_pos.'.'.$item_number;
						$with_group 		= false;
						if(count($total_groups) == 0) {
							$new_grp_position 	= 1;
							$new_group_number	= str_pad($new_grp_position, 2, '0', STR_PAD_LEFT);
	
							$est_grp_data 					= array();
							$est_grp_data['group_pos'] 		= $new_group_number;
							$est_grp_data['group_name'] 	= "Group";
							$est_grp_data['estimation_id'] 	= $project_estimation->id;
							$est_grp_data['position'] 		= $new_grp_position;
	
							$new_group 		= EstimationGroup::create($est_grp_data);
							$last_group  	= $new_group->id;
							$number         = 1;
							$number         = str_pad($number, 2, '0', STR_PAD_LEFT);
							$pos            = $new_group_number.'.'.$number;
							$with_group 	= true;
						}
	
						$newProjectEstimationProduct = ProjectEstimationProduct::create([
							"project_estimation_id" => $project_estimation->id,
							'group_id' => $last_group,
							'type' => 'comment',
							'pos' => $pos
						]);
						$quote = ProjectEstimation::with("estimation_products")->find($project_estimation->id);
						$quote->quoteItem = $quote->estimation_products;
						$estimation_quotes = $project_estimation->all_quotes;
						
						if(count($estimation_quotes) > 0) {
							$insert_array = array();
							$filteredColumnArray = [];
							$estimation_quote_ids = array();
							foreach($estimation_quotes as $estimation_quote) {
								if(isset($estimation_quote->id)) {
									$old_qoute_items = EstimateQuoteItem::select('price','base_price','all_results','smart_template_data')->where('estimate_quote_id', $estimation_quote->id)->get();
									$old_quote_array = array();
									if(count($old_qoute_items) > 0) {
										foreach($old_qoute_items as $old_qoute_item) {
											$old_quate_prices['price'] = $old_qoute_item->price;
											$old_quate_prices['base_price'] = $old_qoute_item->base_price;
											$old_quate_prices['all_results'] = $old_qoute_item->all_results;
											$old_quate_prices['smart_template_data'] = $old_qoute_item->smart_template_data;
											$old_quote_array[] = $old_quate_prices;
										}
									}
									$estimation_quote_ids[] = $estimation_quote->id;
									foreach ($quote->quoteItem as $qi_key => $item) {
										$item_price = isset($old_quote_array[$qi_key]['price']) ? $old_quote_array[$qi_key]['price'] : 0;
										$base_price = isset($old_quote_array[$qi_key]['base_price']) ? $old_quote_array[$qi_key]['base_price'] : 0;
										$total_price = round($item->quantity * $item_price, 2);
										$smart_template_data = isset($old_quote_array[$qi_key]['smart_template_data']) ? $old_quote_array[$qi_key]['smart_template_data'] : NULL;
										$all_results = isset($old_quote_array[$qi_key]['all_results']) ? $old_quote_array[$qi_key]['all_results'] : NULL;
										$insert_item = array(
											"estimate_quote_id" => $estimation_quote->id,
											"product_id" => $item->id,
											"base_price" => $base_price,
											"price" => $item_price,
											"total_price" => $total_price,
											"all_results" => $all_results,
											"smart_template_data" => $smart_template_data,
											"created_at" => date('Y-m-d h:i:s'),
											"updated_at" => date('Y-m-d h:i:s')
										);
										$insert_array[] 		= $insert_item;
	
										if ($item->id === $newProjectEstimationProduct->id) {
											$extraColumn = [
												"estimate_quote_id" => $estimation_quote->id,
												"product_id" => $item->id,
												"unit" => $item->unit,
												"base_price" => $base_price,
												"price" => $item_price,
												"total_price" => $total_price,
											];
	
											$filteredColumnArray[] = $extraColumn;   
										}
									}
								}
							}
							if (count($estimation_quote_ids) > 0) {
								EstimateQuoteItem::whereIn("estimate_quote_id", $estimation_quote_ids)->delete();
							}
							if (count($insert_array) > 0) {
								EstimateQuoteItem::insert($insert_array);
							}
						}
						// $estimation = ProjectEstimation::find($request->estimation_id);
						// $project_estimation->init_status = 1;
						if ($project_estimation->isDirty()) {                    
							$project_estimation->update();
						}
	
						if(isset($request->item_html)) {
							$product = $newProjectEstimationProduct;
							$quote_items_ids = array();
							foreach ($quote->quoteItem as $key => $value) {
								$quote_items_ids[] = $value->id;
							}
							$quote_items = array();
							$result = EstimateQuoteItem::whereIn('product_id', $quote_items_ids)->with('quote')->orderBy('estimate_quote_id')->get();
							foreach($result as $row) {
								if($user->type == "company") {
									if(isset($row->quote->is_display) && $row->quote->is_display == 1) {
										$quote_items[$row->product_id][] = $row;
									}
								} else {
									if(isset($row->quote->user_id) && $row->quote->user_id == $user->id) {
										$quote_items[$row->product_id][] = $row;
									}
								}
							}
	
							$ai_description_field   = null;
							if(isset($request->ai_description_field) && $request->ai_description_field == 1){
								$ai_description_field   = true;        
							}
							$html_data = view('taskly::project_estimations.comment_row', compact('product','quote_items','ai_description_field','with_group'))->render();
							return response(['status' => true, 'message' => __("Comment Added"), 'html_data' => $html_data]);
						} else {
							return response(['status' => true, 'message' => __("Comment Added"), 'latest_project_estimation' => $newProjectEstimationProduct, 'insert_array' => $filteredColumnArray ?? [] ]);
						}
					} else {
						return response(['status' => false, 'message' => "Estimation not found"]);
					}
				} else {
					return response(['status' => false, 'message' => "Estimation id Required"]);
				}
			} else {
				return response()->json(['status' => false, 'message' => __('Permission Denied.')]);
			}
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
	}

	public function remove_items(Request $request)
    {
        try {
			$user	= Auth::user();
			if ($user->isAbleTo('estimation remove option')) {
				if (!empty($request->estimation_id)) {
					$project_estimation = ProjectEstimation::find($request->estimation_id);
					if(isset($project_estimation->id)) {
						$group_ids = isset($request->group_ids) ? json_decode($request->group_ids) : array();
						$item_ids = isset($request->item_ids) ? json_decode($request->item_ids) : array();
						if(count($group_ids) > 0){
							EstimationGroup::whereIn("id", $group_ids)->delete();
						}
						if(count($item_ids) > 0){
							ProjectEstimationProduct::whereIn("id", $item_ids)->delete();
							EstimateQuoteItem::whereIn('product_id', $item_ids)->delete();
						}
	
						return response(['status' => true, 'message' => "Removed"]);
					} else {
						return response(['status' => false, 'message' => "Estimation not found"]);
					}
				} else {
					return response(['status' => false, 'message' => "Estimation id Required"]);
				}
			} else {
				return response()->json(['status' => false, 'message' => __('Permission Denied.')]);
			}
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
    }

	public function reorder_group_modal(Request $request)
    { 
		try {
            if (!empty($request->estimation_id)) {
				$user 					= Auth::user();
                $project_estimation 	= ProjectEstimation::find($request->estimation_id);
				if(isset($project_estimation->id)) {
						$groups = EstimationGroup::with('children')->where('estimation_id', $project_estimation->id)->whereNull('parent_id')->orderBy('position', 'ASC')->get();
						$html_data = view('taskly::project_estimations.reorder_group', compact('project_estimation','groups'))->render();
						return response(['status' => true, 'html_data' => $html_data]);
					
				} else {
					return response(['status' => false, 'message' => "Estimation not found"]);
				}
            } else {
                return response(['status' => false, 'message' => "Estimation id Required"]);
            }
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
	}

	public function store_group_reorder(Request $request)
    {
        try {
            if (!empty($request->estimation_id)) {
                $project_estimation = ProjectEstimation::find($request->estimation_id);
                if(isset($project_estimation->id)) {
					$nestable_data = isset($request->nestable_data) ? $request->nestable_data : array();
					$this->saveNestableItems($nestable_data);

                    return response(['status' => true, 'message' => __("Group reorder successfully")]);
                } else {
                    return response(['status' => false, 'message' => __("Estimation not found")]);
                }
            } else {
                return response(['status' => false, 'message' => __("Estimation id Required")]);
            }
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
    }

	private function saveNestableItems($items, $parentId = null, $parentPos = null)
    {
		foreach ($items as $index => $item) {
			$position 	= $index + 1;
			$group_pos 	= str_pad($position, 2, 0, STR_PAD_LEFT);
			if (isset($parentPos)) {
				$group_pos = $parentPos . '.' . str_pad($group_pos, 2, 0, STR_PAD_LEFT);
			}
			$nestableItem = EstimationGroup::updateOrCreate(
				['id' => $item['id']],
				[
					'parent_id' => $parentId,
					'position' 	=> $position,
					'group_pos' => $group_pos,
				]
			);

			if (isset($item['children'])) {
				$this->saveNestableItems($item['children'], $nestableItem->id, $group_pos);
			}
		}
    }

	public function saveEstimationTitle(Request $request)
    {
        ProjectEstimation::find($request->estimation_id)->update([
            "title" => $request->estimation_title,
            "issue_date" => $request->issue_date,
			"technical_description" => $request->technical_description,
        ]);   
    }

	public function saveTaxInfo($request)
    {
        ProjectEstimation::find($request->estimation_id)->update([
            "title" => $request->estimation_title,
            "issue_date" => $request->issue_date,
            // "init_status" => 1,
        ]);
        // ProjectEstimationProduct::where('project_estimation_id', $request->estimation_id)->delete();
        foreach ($request->all_quotes as $quote) {
            
            EstimateQuote::find($quote['id'])->update([
                "gross" => $request->all_gross['gross_sc' . $quote['id']],
                "gross_with_discount" => $request->all_gross_with_discount['gross_with_discount_sc' . $quote['id']],
                "net" => $request->all_net['net_sc' . $quote['id']],
                "net_with_discount" => $request->all_net_with_discount['net_with_discount_sc' . $quote['id']],
                "tax" => $request->all_tax['tax_sc' . $quote['id']],
                "discount" => $request->all_discount['discount_sc' . $quote['id']],
                "markup" => $request->all_markup['markup_sc' . $quote['id']],
            ]);
            // EstimateQuoteItem::where('estimate_quote_id', $quote['id'])->delete();
        }
    }

	public function saveMultple($request)
    {
        foreach ($request->items as $item) {
            foreach ($request->all_quotes as $k => $quote) {
                EstimateQuoteItem::updateOrCreate([
                        "estimate_quote_id" => $quote['id'],
                        "product_id" => $item['estimation_product_id'],
                ],[
                    'price' => $item['single_price_sc' . $quote['id']] ?? null,
                    'total_price' => $item['total_price_sc' . $quote['id']] ?? null,
                ]);
            }

            ProjectEstimationProduct::updateOrCreate([
                    'id' => $item['estimation_product_id']
            ],[
                "project_estimation_id" => $request->estimation_id,
                'name' => $item['name'] ?? null,
                'description' => $item['description'] ?? null,
                'pos' => $item['pos'] ?? null,
                'quantity' => $item['quantity'] ?? null,
                'unit' => $item['unit'] ?? null,
                'comment' => $item['comment'] ?? null,
                'campare_percent' => $item['campare_percent'] ?? null,
            ]);
        }
    }

	public function saveFinalize(Request $request)
    {
		$user = Auth::user();
        $this->saveTaxInfo($request);

        if (!empty($request->multiple) && $request->multiple == true) {
            $this->saveMultple($request);
        }else{

            $item = $request->items[0] ?? null;
            if(!empty($item)) {
                $price = 0;
                foreach ($request->all_quotes as $k => $quote) {
                    $is_optional    = ($item['optional'] == true) ? 0 : 1;
                    $price          = $item['single_price_sc' . $quote['id']] ?? null;
                    $total_price    = $item['total_price_sc' . $quote['id']] ?? null;
                    $item_name      = $item['name'] ?? null;

                    EstimateQuoteItem::updateOrCreate([
                        "estimate_quote_id" => $quote['id'],
                        "product_id" => $item['estimation_product_id'],
                    ],[
                        'price' => $price,
                        'base_price' => $price,
                        'total_price' => $total_price,
                    ]);
                }

                $product = ProjectEstimationProduct::updateOrCreate([
                    'id' => $item['estimation_product_id']
                ],[
                    "project_estimation_id" => $request->estimation_id,
                    'name' => $item_name,
                    'description' => $item['description'] ?? null,
                    'pos' => $item['pos'] ?? null,
                    'quantity' => $item['quantity'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'is_optional' => $is_optional,
                    'comment' => $item['comment'] ?? null,
                    'campare_percent' => $item['campare_percent'] ?? null,
                ]);

            }
        }

		if($user->type != "company") {
			EstimateQuote::where('project_estimation_id', $request->estimation_id)->where('user_id',  Auth::user()->id)->update([
				'is_display' => 1
			]);
		}

        return response(true);
    }

	public function finalizeEstimation($id)
    {
        $id 		= Crypt::decrypt($id);
        $estimation = ProjectEstimation::whereId($id)->first();
    //    $settings 	= Utility::settings();
		$settings 	= array();
        $quote 		= EstimateQuote::with("quoteItem")->where("project_estimation_id", $id)->where("is_final", 1)->first();
        // Estimation Email
       	$estimateEmailTemplate 	= getNotificationTemplateData('estimation_email');
       	$estimatePdfTopTemplate = getNotificationTemplateData('estimation_pdf_top');
       	$estimatePdfEndTemplate = getNotificationTemplateData('estimation_pdf_end');
        $site_money_format 		= site_money_format();

        $additional_files_list = "";
        $additional_files_path = public_path('additional_files');
        if(File::isDirectory($additional_files_path)){
            $additional_files_list = File::files($additional_files_path);
        }

        $project_files 			= '';
        $project_images_files 	= array();
        $project_other_files 	= array();
        if (isset($estimation->project_id) && !empty($estimation->project_id)) {
            $project_files 		= ProjectFile::where('project_id', $estimation->project_id)->get();

            if (!empty($project_files)) {
                foreach ($project_files as $prow) {
                    $file_extension = strtolower(pathinfo($prow->file_name, PATHINFO_EXTENSION));
                    $is_image 		= in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'heif']);
                    if ($is_image) {
                        $project_images_files[] = $prow;
                    } else {
                        $project_other_files[] 	= $prow;
                    }
                }
            }
        }

        $filters_request['order_by'] = array('field' => 'projects.created_at', 'order' => 'DESC');
        $project_record = Project::get_all($filters_request);
        $all_projects = isset($project_record['records']) ? $project_record['records'] : array();
		$company_details = getCompanyAllSetting();

        return view("taskly::project_estimations.finalize", compact('estimation', 'quote', 'settings', 'estimateEmailTemplate', 'estimatePdfTopTemplate', 'estimatePdfEndTemplate', 'site_money_format', 'additional_files_list', 'project_images_files', 'project_other_files', 'all_projects','company_details'));
    }

	public function calculateMarkup(Request $request)
    {
        $markup = $request->markup / 100;
        $quote_id = $request->quote_id;
        $type = $request->type;
        $estimationItems = [];
        
        foreach ($request->data as $item) {
            $data = $item;
            if(isset($type)) {
                if($type == 'quote'){
                    if (isset($item['single_price_sc' . $quote_id]) && is_numeric($item['single_price_sc' . $quote_id])) {
                        $price_sc = $data['single_price_sc' . $quote_id];
                        $new_single_price = $price_sc + ($price_sc * $markup);
                        $data['single_price_sc' . $quote_id] = currency_format_with_sym($new_single_price,'','',false);
                        if (isset($item['single_price_sc_input' . $quote_id]) && $item['single_price_sc_input' . $quote_id] > 0) {
                             $price_input       = '<input type="text" class="form-control row_price single_price_sc'.$quote_id .' single_price_sc_input_'. $item['item_id'] .'_'. $quote_id .'" data-id="'.$quote_id .'" value="'.currency_format_with_sym($new_single_price,'','',false).'">';
                            $data['single_price_sc_input' . $quote_id] = $price_input;
                        }
                    }
                    if (isset($item['total_price_sc' . $quote_id]) && is_numeric($item['total_price_sc' . $quote_id])) {
                        $total_price_sc = $data['total_price_sc' . $quote_id];
                        $data['total_price_sc' . $quote_id] = $total_price_sc + ($total_price_sc * $markup);
                    }
                }else{
                    $price = $data['price'];
                    $data['price'] = $price + ($price * $markup);
                    $total_price = $data['totalPrice'];
                    $data['totalPrice'] = $total_price + ($total_price * $markup);
                }
            }
            $estimationItems[] = $data;
        }
        return $estimationItems;
    }

	public function clone(Request $request)
    {
		$user	= Auth::user();
		if ($user->isAbleTo('estimation duplicate quote option')) {
			if($request->type == "quote") {
				$quote = EstimateQuote::with("quoteItem")->find($request->clone_id);
			}else{
				$quote = ProjectEstimation::with("estimation_products")->find($request->clone_id);
				$quote->quoteItem = $quote->estimation_products;
				$quote->project_estimation_id = $quote->id;
			}
	
			$quote_title = isset($request->title) ? $request->title : '';
			if(isset($request->sub_contractor)){
				$user_data = User::find($request->sub_contractor);
				if(isset($user_data->id)){
					$quote_title = $user_data->name;
				}
			}
	
			$new_quote = EstimateQuote::create([
				"title" => $quote_title,
				"user_id" => $request->sub_contractor,
				"net" => $quote->net + ($quote->net * $request->markup / 100),
				"net_with_discount" => $quote->net_with_discount + ($quote->net_with_discount * $request->markup / 100),
				"gross" => $quote->gross + ($quote->gross * $request->markup / 100),
				"gross_with_discount" => $quote->gross_with_discount + ($quote->gross_with_discount * $request->markup / 100),
				"discount" => 2,
				"tax" => 19,
				"is_clone" => 1,
				"markup" => $request->markup,
				"project_estimation_id" => $quote->project_estimation_id,
				"project_id" => $quote->project_id
			]);
	
			$insert_array = array();
			foreach ($quote->quoteItem as $item) {
				$insert_item = array(
					"estimate_quote_id" => $new_quote->id,
					"product_id" => ($request->type == 'quote')? $item->product_id : $item->id,
					"price" => $item->price,
					"base_price" => $item->base_price,
					"total_price" => $item->total_price,
					"created_at" => date('Y-m-d h:i:s'),
					"updated_at" => date('Y-m-d h:i:s')
				);
				$insert_array[]         = $insert_item;
			}
			if (count($insert_array) > 0) {
				EstimateQuoteItem::insert($insert_array);
			}
			$estimation = ProjectEstimation::find($request->estimation_id);
			$estimation->save();
			$title = isset($new_quote->user->name) ? $new_quote->user->name : $new_quote->title;
			$all_quotes = EstimateQuote::where("project_estimation_id", $request->estimation_id)->orderBy('id', 'ASC')->get();
			return \response(["quote" => $new_quote, 'items' => $new_quote->quoteItem()->get(), 'all_quotes' => $all_quotes, 'title' => $title]);
		} else {
			return response()->json(['error' => __('Permission denied.')], 401);
		}
    }

	public function editClone(Request $request)
    {
        $title = $request->title;
        if(isset($request->sub_contractor) && $request->sub_contractor > 0) {
            if(empty($title)) {
                $user_data = User::find($request->sub_contractor);
                if(isset($user_data->id)){
                    $title = $user_data->name;
                }
            }
        //  $title = '';
        }

        EstimateQuote::findOrFail($request->quote_id)->update([
            "title" => $title,
            "user_id" => $request->sub_contractor,
            "markup" => $request->markup
        ]);
        $quote =  EstimateQuote::find($request->quote_id);
        $quotes = EstimateQuote::where('project_estimation_id', $request->estimation_id)->with("user")->get();
        return ['items' => $this->calculateMarkup($request), 'quotes' => $quotes, 'quote' => $quote];
    }

	public function deleteQuote(Request $request)
    {
        EstimateQuoteItem::where('estimate_quote_id', $request->quote_id)->delete();
        EstimateQuote::findOrFail($request->quote_id)->delete();
        $quotes = EstimateQuote::where('project_estimation_id', $request->estimation_id)->with("user")->get();
        return \response($quotes);
    }

	public function finalizeQuote(Request $request)
    {
        $quote = EstimateQuote::find($request->id);
        if (isset($request->type) && $request->type == 'client') {
			EstimateQuote::where('project_id', $quote->project_id)->update([
                "final_for_client" => 0
            ]);
			ProjectEstimation::where('project_id', $quote->project_id)->update(['is_active'=> 0]);
			ProjectEstimation::find($quote->project_estimation_id)->update(['is_active'=> 1]);
			$quote->final_for_client = 1;
            $quote->save();
            return $request->id;
        } elseif (isset($request->type) && $request->type == 'sub_contractor') {
            EstimateQuote::where('project_id', $quote->project_id)->update([
                "final_for_sub_contractor" => 0
            ]);
			$quote->final_for_sub_contractor = 1;
            $quote->save();
            return $request->id;
        } else {
            EstimateQuote::where('project_estimation_id', $quote->project_estimation_id)->update([
                "is_final" => 0
            ]);
            $quote->is_final = 1;
            $quote->save();
            return $quote;
        }
    }

	public function updateGrpname(Request $request)
    {
		EstimationGroup::findOrFail($request->group_id)->update([
            'group_name' => $request->grpname
        ]);

        return response(true);
    }

	public function handlePosSaveOrder(Request $request)
    {
		try {
			$projectEstimationId = Crypt::decrypt($request->project_estimation_id);
		} catch (\Exception $e) {
			$projectEstimationId = $request->project_estimation_id;
		}

		$last_group_pos    = "";
		$second_index       = 0;
		$all_new_pos = array();
		if(isset($projectEstimationId) && count($projectEstimationId) > 0) {
			foreach ($projectEstimationId as $index => $id_grp_pos_name) {
				$explode_data   = explode("_", $id_grp_pos_name);
				$id             = $explode_data[0];
				$grp_id       	= $explode_data[1];
				$grp_pos       	= $explode_data[2];
				$new_index      = $index + 1;
	
				if ($last_group_pos == '') {
					$last_group_pos = $grp_pos;
				}
				if ($grp_pos != $last_group_pos) {
					$last_group_pos = $grp_pos;
					$second_index = 0;
				}
				$second_index++;
	
				$pos = $last_group_pos . '.' . str_pad($second_index, 2, 0, STR_PAD_LEFT);
				$newpos = $pos;
	
				DB::table('project_estimation_products')
				->where('id', $id)
				->update([
					'position'  => $second_index,
					'pos'       => $pos,
					'group_id' 	=> $grp_id,
				]);
	
				$all_new_pos[$index] = $newpos;
			}
		}

		if (isset($request->item_html)) {
			return response()->json(['status' => true, 'all_new_pos' => $all_new_pos]);
		} else {
			return response()->json(['status' => true]);
		}
    }

	public function exportEstimationInExcel($id = '', $type = '')
	{
		$user	= Auth::user();
		if ($user->isAbleTo('estimation download option')) {
			$encryptId 	= $id;
			$id 		= Crypt::decrypt($id);
			if (!empty($encryptId)) {
				$estimation_products 	= ProjectEstimationProduct::where('type', 'item')->where('project_estimation_id', $id)->get();
				$estimation 			= ProjectEstimation::with('estimation_groups')->find($id);
				$estimation_result 		= array();
				$cnt 					= 1;
				if ((isset($type) && $type == 'attachment-download') || (isset($type) && $type == 'attach')) {
					$quote 			= EstimateQuote::with("quoteItem")->where("project_estimation_id", $id)->where("is_final", 1)->first();
					$quote_items 	= array();
					foreach ($quote->quoteItem()->orderBy('id')->get() as $key => $q_item) {
						$quote_items[$q_item->product_id] = $q_item;
					}
				} else {
					$quote_items_ids = array();
					foreach ($estimation_products as $key => $value) {
						$quote_items_ids[] = $value->id;
					}
					$quote_items 	= array();
					$result 		= EstimateQuoteItem::whereIn('product_id', $quote_items_ids)->with('quote')->orderBy('estimate_quote_id')->get();
					foreach ($result as $row) {
						$quote_items[$row->product_id][] = $row;
					}
				}
				foreach ($estimation->estimation_groups()->orderBy('group_pos')->get() as $key => $item_group) {
					foreach ($item_group->estimation_products as $value) {
						$row = array();
						$row['id'] 			= $cnt;
						$row['pos'] 		= $value->pos;
						$row['position'] 	= $value->position;
						$row['group_pos'] 	= $item_group->group_pos;
						$row['name'] 		= $value->name;
						$row['description'] = $value->description;
						$row['quantity'] 	= $value->quantity;
						$row['unit'] 		= $value->unit;
						$row['optional'] 	= $value->is_optional;
						$row['group_name'] 	= $item_group->group_name;
						$row['type'] 		= $value->type;
						$row['comment'] 	= $value->comment;
						$row['quote_lists'] = array();
						if (isset($quote_items[$value->id])) {
							$quotations_details = array();
							if ((isset($type) && $type == 'attachment-download') || (isset($type) && $type == 'attach')) {
								$row['quote_lists'][$quote_items[$value->id]->estimate_quote_id]['single_price'] = export_money_format($quote_items[$value->id]->price);
								$item_total = ($value->is_optional == 0) ? 0 : export_money_format($quote_items[$value->id]->total_price);
								$row['quote_lists'][$quote_items[$value->id]->estimate_quote_id]['total_price'] = export_money_format($item_total);
								$row['quote_lists'][$quote_items[$value->id]->estimate_quote_id]['quote_name'] = $quote_items[$value->id]->quote->title;
								$quotations_details[$quote_items[$value->id]->estimate_quote_id]['title'] = $quote_items[$value->id]->quote->title;
								$quotations_details[$quote_items[$value->id]->estimate_quote_id]['gross'] = export_money_format($quote_items[$value->id]->quote->gross);
								$quotations_details[$quote_items[$value->id]->estimate_quote_id]['gross_with_discount'] = export_money_format($quote_items[$value->id]->quote->gross_with_discount);
								$quotations_details[$quote_items[$value->id]->estimate_quote_id]['net'] = export_money_format($quote_items[$value->id]->quote->net);
								$quotations_details[$quote_items[$value->id]->estimate_quote_id]['net_with_discount'] = export_money_format($quote_items[$value->id]->quote->net_with_discount);
								$quotations_details[$quote_items[$value->id]->estimate_quote_id]['tax'] = ($quote_items[$value->id]->quote->tax);
								$quotations_details[$quote_items[$value->id]->estimate_quote_id]['discount'] = export_money_format($quote_items[$value->id]->quote->discount);
								$quotations_details[$quote_items[$value->id]->estimate_quote_id]['markup'] = export_money_format($quote_items[$value->id]->quote->markup);
								$quotations_details[$quote_items[$value->id]->estimate_quote_id]['vat_amount'] = export_money_format($quote_items[$value->id]->quote->gross - $quote_items[$value->id]->quote->net);
								$quotations_details[$quote_items[$value->id]->estimate_quote_id]['discount_amount'] = export_money_format($quote_items[$value->id]->quote->gross - $quote_items[$value->id]->quote->gross_with_discount);
							} else {
								foreach ($quote_items[$value->id] as $quoteItem) {
									$row['quote_lists'][$quoteItem->estimate_quote_id]['single_price'] = export_money_format($quoteItem->price);
									$item_total = ($value->is_optional == 0) ? 0 : export_money_format($quoteItem->total_price);
									$row['quote_lists'][$quoteItem->estimate_quote_id]['total_price'] = export_money_format($item_total);
									$row['quote_lists'][$quoteItem->estimate_quote_id]['quote_name'] = $quoteItem->quote->title;
									$quotations_details[$quoteItem->estimate_quote_id]['title'] = $quoteItem->quote->title;
									$quotations_details[$quoteItem->estimate_quote_id]['gross'] = export_money_format($quoteItem->quote->gross);
									$quotations_details[$quoteItem->estimate_quote_id]['gross_with_discount'] = export_money_format($quoteItem->quote->gross_with_discount);
									$quotations_details[$quoteItem->estimate_quote_id]['net'] = export_money_format($quoteItem->quote->net);
									$quotations_details[$quoteItem->estimate_quote_id]['net_with_discount'] = export_money_format($quoteItem->quote->net_with_discount);
									$quotations_details[$quoteItem->estimate_quote_id]['tax'] = ($quoteItem->quote->tax);
									$quotations_details[$quoteItem->estimate_quote_id]['discount'] = export_money_format($quoteItem->quote->discount);
									$quotations_details[$quoteItem->estimate_quote_id]['markup'] = export_money_format($quoteItem->quote->markup);
								}
							}
							$estimation_result[] = $row;
							$cnt++;
						}
					}
				}
				if (!empty($estimation_result)) {
					$excel_file_name = get_file_name($id);
					$file_name = $excel_file_name . '.xlsx';
					if (isset($type) && $type == 'attach') {
						$file_path = "export/" . $file_name;
						Excel::store(new ProjectEstimationExport($estimation_result, $quotations_details, $type), $file_path);
						return $file_name;
					} else {
						return Excel::download(new ProjectEstimationExport($estimation_result, $quotations_details, $type), $file_name);
					}
				} else {
					return response(['status' => false, 'message' => __("Estimation not found")]);
				}
			} else {
				return response(['status' => false, 'message' => __("Estimation id Required")]);
			}
		} else {
			return response(['status' => false, 'message' => __("Permission Denied.")]);
		}
	}

	public function exportEstimationInCSV($id = '', $type = '')
	{
		$user	= Auth::user();
		if ($user->isAbleTo('estimation download option')) {
			$encryptId = $id;
			$id = Crypt::decrypt($id);
			if (!empty($encryptId)) {
				$estimation_products = ProjectEstimationProduct::where('type', 'item')->where('project_estimation_id', $id)->get();
				$estimation = ProjectEstimation::with('estimation_groups')->find($id);
				if ((isset($type) && $type == 'attachment-download') || (isset($type) && $type == 'attach')) {
					$quote = EstimateQuote::with("quoteItem")->where("project_estimation_id", $id)->where("is_final", 1)->first();
					$quote_items = array();
					foreach ($quote->quoteItem()->orderBy('id')->get() as $key => $q_item) {
						$quote_items[$q_item->product_id] = $q_item;
					}
				} else {
					$quote_items_ids = array();
					foreach ($estimation_products as $key => $value) {
						$quote_items_ids[] = $value->id;
					}
					$quote_items = array();
					$result = EstimateQuoteItem::whereIn('product_id', $quote_items_ids)->with('quote')->orderBy('estimate_quote_id')->get();
					foreach ($result as $row) {
						$quote_items[$row->product_id][] = $row;
					}
				}
				$csv_file_name = get_file_name($id);
				$file_name = $csv_file_name . '.csv';
				$file_path = "uploads/export/" . $file_name;
				$headers = [
					'Content-Type' => 'text/csv',
					'Content-Disposition' => "attachment; filename=\"$file_name\"",
					'Pragma' => 'no-cache',
					'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
					'Expires' => '0',
				];
				// $export_csv_data = function () use ($estimation, $quote_items, $file_path, $type) {
				if (!File::exists(dirname($file_path))) {
					File::makeDirectory(dirname($file_path), 0755, true, true);
				}
				$csv_data 			= fopen($file_path, 'w');
				$cnt 				= 1;
				$estimation_result 	= array();
				foreach ($estimation->estimation_groups()->orderBy('group_pos')->get() as $key2 => $item_group) {
					foreach ($item_group->estimation_products as $key => $value) {
						$estimation_result[$cnt]['cnt'] 		= $cnt;
						$estimation_result[$cnt]['group_name'] 	= $item_group->group_name;
						$estimation_result[$cnt]['pos'] 		= $value->pos;
						$estimation_result[$cnt]['name'] 		= $value->name;
						$estimation_result[$cnt]['description'] = $value->description;
						$estimation_result[$cnt]['quantity'] 	= $value->quantity;
						$estimation_result[$cnt]['unit'] 		= $value->unit;
						$estimation_result[$cnt]['is_optional'] = $value->is_optional;
						$quotations_details = array();
						if ((isset($type) && $type == 'attachment-download') || (isset($type) && $type == 'attach')) {
							// foreach ($quote_items[$value->id] as $quoteItem) {
							$estimation_result[$cnt]['quote_lists'][$quote_items[$value->id]->estimate_quote_id]['single_price'] = export_money_format($quote_items[$value->id]->price);
							$item_total = ($value->is_optional == 0) ? 0 : $quote_items[$value->id]->total_price;
							$estimation_result[$cnt]['quote_lists'][$quote_items[$value->id]->estimate_quote_id]['total_price'] = export_money_format($item_total);
							$estimation_result[$cnt]['quote_lists'][$quote_items[$value->id]->estimate_quote_id]['quote_name'] = $quote_items[$value->id]->quote->title;
							$quotations_details[$quote_items[$value->id]->estimate_quote_id]['title'] = $quote_items[$value->id]->quote->title;
							$quotations_details[$quote_items[$value->id]->estimate_quote_id]['gross'] = export_money_format($quote_items[$value->id]->quote->gross);
							$quotations_details[$quote_items[$value->id]->estimate_quote_id]['gross_with_discount'] = export_money_format($quote_items[$value->id]->quote->gross_with_discount);
							$quotations_details[$quote_items[$value->id]->estimate_quote_id]['net'] = export_money_format($quote_items[$value->id]->quote->net);
							$quotations_details[$quote_items[$value->id]->estimate_quote_id]['net_with_discount'] = export_money_format($quote_items[$value->id]->quote->net_with_discount);
							$quotations_details[$quote_items[$value->id]->estimate_quote_id]['tax'] = $quote_items[$value->id]->quote->tax;
							$quotations_details[$quote_items[$value->id]->estimate_quote_id]['discount'] = export_money_format($quote_items[$value->id]->quote->discount);
							$quotations_details[$quote_items[$value->id]->estimate_quote_id]['markup'] = export_money_format($quote_items[$value->id]->quote->markup);
							$quotations_details[$quote_items[$value->id]->estimate_quote_id]['vat_amount'] = export_money_format($quote_items[$value->id]->quote->gross - $quote_items[$value->id]->quote->net);
							$quotations_details[$quote_items[$value->id]->estimate_quote_id]['discount_amount'] = export_money_format($quote_items[$value->id]->quote->gross - $quote_items[$value->id]->quote->gross_with_discount);
							// }
						} else {
							foreach ($quote_items[$value->id] as $quoteItem) {
								$estimation_result[$cnt]['quote_lists'][$quoteItem->estimate_quote_id]['single_price'] = export_money_format($quoteItem->price);
								$item_total = ($value->is_optional == 0) ? 0 : $quoteItem->total_price;
								$estimation_result[$cnt]['quote_lists'][$quoteItem->estimate_quote_id]['total_price'] = export_money_format($item_total);
								$estimation_result[$cnt]['quote_lists'][$quoteItem->estimate_quote_id]['quote_name'] = $quoteItem->quote->title;
								$quotations_details[$quoteItem->estimate_quote_id]['title'] = $quoteItem->quote->title;
								$quotations_details[$quoteItem->estimate_quote_id]['gross'] = export_money_format($quoteItem->quote->gross);
								$quotations_details[$quoteItem->estimate_quote_id]['gross_with_discount'] = export_money_format($quoteItem->quote->gross_with_discount);
								$quotations_details[$quoteItem->estimate_quote_id]['net'] = export_money_format($quoteItem->quote->net);
								$quotations_details[$quoteItem->estimate_quote_id]['net_with_discount'] = export_money_format($quoteItem->quote->net_with_discount);
								$quotations_details[$quoteItem->estimate_quote_id]['tax'] = $quoteItem->quote->tax;
								$quotations_details[$quoteItem->estimate_quote_id]['discount'] = export_money_format($quoteItem->quote->discount);
								$quotations_details[$quoteItem->estimate_quote_id]['markup'] = export_money_format($quoteItem->quote->markup);
							}
						}
						$cnt++;
					}
				}
				/*** csv column ***/
				if (!empty($quotations_details)) {
					$header_columns = [__("Pos"), __("Group Name"), __("Name"), __("Description"), __("Quantity"), __("Unit"), __("Optional")];
					$empty_row 	= [' '];
					$group_quote_title = [' ', ' ', ' ', ' ', ' ', ' ', ' '];
					$net_incl_discount = ["", "", "", "", "", "", __("Net incl. Discount"), ""];
					$gross_incl_discount = ["", "", "", "", "", "", __("Gross incl. Discount"), ""];
					$net = ["", "", "", "", "", "", __("Net"), ""];
					$gross = ["", "", "", "", "", "", __("Gross (incl. VAT)")];
					$markup_discount_title = ["", "", "", "", "", "", ""];
					$markup_discount_value = ["", "", "", "", "", "", ""];
					if ((isset($type) && $type == 'attachment-download') || (isset($type) && $type == 'attach')) {
						$vat_amount_title = ["", "", "", "", "", "", __('VAT'), ""];
						$cash_discount_amount_title = ["", "", "", "", "", "", __('Cash Discount'), ""];
					}
					foreach ($quotations_details as $row) {
						array_push($net_incl_discount, iconv("UTF-8", "CP1252", $row['net_with_discount']));
						array_push($net_incl_discount, "");
						array_push($gross_incl_discount, iconv("UTF-8", "CP1252", $row['gross_with_discount']));
						array_push($gross_incl_discount, "");
						array_push($net, iconv("UTF-8", "CP1252", $row['net']));
						array_push($net, "");
						array_push($gross, $row['tax'] . '%');
						array_push($gross, iconv("UTF-8", "CP1252", $row['gross']));
						if ((isset($type) && $type == 'attachment-download') || (isset($type) && $type == 'attach')) {
							array_push($markup_discount_title, "");
							array_push($markup_discount_value, "");
							array_push($vat_amount_title, $row['vat_amount']);
							array_push($cash_discount_amount_title, $row['discount_amount']);
						} else {
							array_push($markup_discount_title, "Markup");
							array_push($markup_discount_value, $row['markup']);
						}
						array_push($markup_discount_title, "Discount");
						array_push($markup_discount_value, $row['discount']);
						array_push($group_quote_title, $row['title']);
						array_push($group_quote_title, "");
						array_push($header_columns, __("Single Price"));
						array_push($header_columns, __("Total Price"));
					}
					fputcsv($csv_data, $net_incl_discount);
					fputcsv($csv_data, $gross_incl_discount);
					fputcsv($csv_data, $net);
					fputcsv($csv_data, $gross);
					if ((isset($type) && $type == 'attachment-download') || (isset($type) && $type == 'attach')) {
						fputcsv($csv_data, $vat_amount_title);
						fputcsv($csv_data, $cash_discount_amount_title);
					}
					fputcsv($csv_data, $markup_discount_title);
					fputcsv($csv_data, $markup_discount_value);
					fputcsv($csv_data, $empty_row);
					fputcsv($csv_data, $group_quote_title);
					fputcsv($csv_data, $header_columns);
				}
				/*** csv data ***/
				if (!empty($estimation_result)) {
					foreach ($estimation_result as $key => $row) {
						$data = [
							$row['pos'],
							$row['group_name'],
							$row['name'],
							$row['description'],
							$row['quantity'],
							$row['unit'],
							$row['is_optional'],
						];
						foreach ($row['quote_lists'] as $key => $qrow) {
							array_push($data, iconv("UTF-8", "CP1252", $qrow['single_price']));
							array_push($data, iconv("UTF-8", "CP1252", $qrow['total_price']));
						}
						fputcsv($csv_data, $data);
					}
				}
				fclose($csv_data);
				// };
				if (isset($type) && $type == 'attach') {
					return $file_name;
				} else {
					$response = FacadesResponse::download($file_path, $file_name, $headers);
					app()->terminating(function () use ($file_path) {
						if (file_exists($file_path)) {
							unlink($file_path);
						}
					});
					return $response;
					// return response()->stream($file_path, 200, $headers);
				}
			} else {
				return response(['status' => false, 'message' => __("Estimation id Required")]);
			}
		} else {
			return response(['status' => false, 'message' => __("Permission Denied.")]);
		}
	}

	public function exportEstimationInGaeb($id = '', $type = '')
	{
		$user	= Auth::user();
		if ($user->isAbleTo('estimation download option')) {
			$encryptId = $id;
			$id = Crypt::decrypt($id);
			if (!empty($encryptId)) {
				$estimation_products 	= ProjectEstimationProduct::where('type', 'item')->where('project_estimation_id', $id)->get();
				$estimation 			= ProjectEstimation::with('estimation_groups')->find($id);
				$quote 					= EstimateQuote::with("quoteItem")->where("project_estimation_id", $id)->where("is_final", 1)->first();
				$quote_items 			= array();
				foreach ($quote->quoteItem()->orderBy('id')->get() as $key => $q_item) {
					$quote_items[$q_item->product_id] = $q_item;
				}
				$estimation_result 		= array();
				$cnt 					= 1;
				$quote_items_ids 		= array();
				foreach ($estimation_products as $key => $value) {
					$quote_items_ids[] = $value->id;
				}
				$quotes_items_list = array();
				$result = EstimateQuoteItem::whereIn('product_id', $quote_items_ids)->with('quote')->orderBy('estimate_quote_id')->get();
				foreach ($result as $row) {
					$quotes_items_list[$row->product_id][] = $row;
				}
				$groups = EstimationGroup::with('children')->where('estimation_id', $id)->whereNull('parent_id')->orderBy('group_pos', 'ASC')->get();
				$glist 	= array();
				foreach ($groups as $group) {
					$glist[] = $this->estimation_groups_wise_details($group);
				}
				$estimation_result = $glist;
				if (!empty($estimation_result)) {
					$site_money_format 	= site_money_format();
					$money_format 		= ($site_money_format == "en_US") ? 'USD' : 'EUR';
					$xml_output 		= view('taskly::project_estimations.export.gaeb')->with(compact('estimation_result', 'quote_items', 'money_format', 'site_money_format', 'quotes_items_list'))->render();
	
					$gaeb_file_name = get_file_name($id);
					$file_name 		= "";
					$file_name 		= $gaeb_file_name . '.x83';
					$file_path 		= "";
					$file_path 		= "export/" . $file_name;
					Storage::disk('local')->put($file_path, $xml_output);
					if (isset($type) && $type == 'attach') {
						return $file_name;
					} else {
						$storage_path = "uploads/export/" . $file_name;
						return response()->download($storage_path, $file_name)->deleteFileAfterSend(true);
					}
				} else {
					return response(['status' => false, 'message' => __("Estimation not found")]);
				}
			} else {
				return response(['status' => false, 'message' => __("Estimation id Required")]);
			}
		} else {
			return response(['status' => false, 'message' => __("Permission Denied.")]);
		}
	}

	function estimation_groups_wise_details($data){
        $return_data = array();
        $return_data['group_name'] = $data->group_name;
        $return_data['group_pos'] = $data->group_pos;
        $return_data['estimation_products'] = $data->estimation_products;
        if ($data->children_data->isNotEmpty()) {
            $children = array();
            foreach ($data->children_data as $sub_group) {
                $children[] = $this->estimation_groups_wise_details($sub_group);
            }
            $return_data['children'] = $children;
        }
        return $return_data;
    }

	public function createEstimationImagesZip(Request $request){
        $id = '';
        $type = '';
        if (!empty($request->id)) {
            $id = $request->id;
            $type = $request->type;
            $project_files_ids = array();
            $project_images_files = '';
            if (isset($request->project_images) && !empty($request->project_images)) {
                $files = '';
                $files = explode(",", $request->project_images);
                foreach ($files as $prow) {
                    $project_files_ids[] = decrypt($prow);
                }
                $project_images_files = ProjectFile::whereIn('id', $project_files_ids)->get();
            }
            return $this->exportEstimationImagesInZip(Crypt::encrypt($id), $type, $project_images_files);
        }
    }

	public function exportEstimationImagesInZip($id = '', $type = '', $project_images){
        $id = Crypt::decrypt($id);
        if (!empty($id)) {
            $zip_file_name = get_file_name($id);
            $file_name = $zip_file_name . '.zip';
            $file_path = "uploads/export/" . $file_name;
            if (!File::exists('uploads/export/')) {
				File::makeDirectory('uploads/export/', 0777, true, true);
            }
            if (!empty($project_images)) {
                $zip = new ZipArchive();
                foreach ($project_images as $key => $row) {
                    $image_file = "";
                    $image_file = 'uploads/projects/' . rawurlencode($row['file_name']);
					if ($zip->open($file_path, ZipArchive::CREATE) === TRUE) {
                        if (File::exists($image_file)) {
                            $zip->addFile($image_file, $row['file']);
                            $zip->close();
                        }
                    }
                }
            }
            if (isset($type) && $type == 'attach') {
                return $file_name;
            } else {
                if (File::exists($file_path)) {
                    return response()->download(($file_path), $file_name)->deleteFileAfterSend(true);
                } else {
                    return redirect()->back()->with('error', __('Someting went wrong.'));
                }
            }
        }
    }

	public function generatePDF($data, $download = false)
    { 
        $path = $data['path'];
        unset($data['path']);
        $pdf = PDF::loadView('taskly::project_estimations.pdf.estimation_for_client_pdf', $data);
        $dir = storage_path($path);
		$dir = $path;
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        // $filename = 'quote' . $data['quote']->id . '-' . time() . '.pdf';
        $filename = $data['file_name'];
        $dir .= $filename;
        $path .= $filename;
        if ($download) {
            return $pdf->download($filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
            // return $pdf->stream($filename, [
            //     'Content-Type' => 'application/pdf',
            //     'Content-Disposition' => 'inline; filename="' . $filename . '"',
            // ]);
        }
        $pdf->save($dir);
        return $dir;

    }

	public function remove_estimation_user(Request $request)
    {
		$user 		= Auth::user();
		if ($user->isAbleTo('estimation delete')) {
			$user_id    	= $request->user_id;
			$estimation_id = $request->estimation_id;
	
			if ($user_id != '' && $estimation_id != '') {
				$user = User::findOrFail($user_id);
				$user_name = "";
				if(isset($user->id)) {
					$user_name = $user->name;
				}
				EstimateQuote::where('project_estimation_id', $estimation_id)->where('user_id', $user_id)->update([
					"title" => $user_name . ' (Removed)',
					"user_id" => NULL
				]);
				// SubContractorEstimation::where('project_id',$project_id)->where('project_estimation_id', $estimation_id)->where('user_id', $user_id)->delete();
	
				return response()->json(['status' => true, 'message' => __('User removed from estimation.')]);
			} else {
				return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
			}
		} else {
			return response()->json(['status' => false, 'message' => __('Permission Denied.')]);
		}
    }

	public function sendClient(Request $request){
        ini_set("max_execution_time", "-1");
        ini_set("memory_limit", "-1");
        $estimation         = ProjectEstimation::whereId($request->id)->first();
        //$settings           = Utility::settings();
		$settings           = array();
		$company_details 	= getCompanyAllSetting();
        $project            = $estimation->project();
        $client             = $project->client_data;
        $quote              = EstimateQuote::with("quoteItem")->where("project_estimation_id", $request->id)->where("is_final", 1)->first();

        EstimateQuote::where("project_estimation_id", $estimation->id)->update(['project_id'=> $estimation->project_id]);

        EstimateQuote::where("project_id", $quote->project_id)->update(['is_official_final'=> 0]);

        $quote->is_official_final = 1;
        $quote->save();

        $success_message = "Quote save successfully";
        if ($request->type == "pdf" || $request->type == "email") {

            $constructionDetail = isset($project->construction_detail) ? $project->construction_detail : null;
            $contractor         = $quote->subContractor;
            
            $path = 'uploads/quotes/';
            
            $client_name = isset($client->name) ? $client->name : '';
            $client_email = isset($request->client_email) ? $request->client_email : '';

            $clientCompanyName = '';
            $clientSalutationTitle = '';
            $clientAcademicTitle = '';
            $clientFirstName = '';
            $clientLastName = '';
            $clientEmail = '';
            $clientPhone = '';
            $clientMobile = '';
            $clientWebsite = '';
            $constructionStreetN = '';
            $constructionAdditionalAddress = '';
            $constructionZipcode = '';
            $constructionCity = '';
            $constructionState = '';
            $constructionCountry = '';
            $constructionTaxNumber = '';
            $constructionTaxNotes = '';
            $clientSalutation = "";
            $constructionSalutation = "";
            
            if (isset($constructionDetail) && $constructionDetail !=null){
                $clientCompanyName = (isset($client->company_name) && !empty($client->company_name)) ? $client->company_name : '';
                $clientSalutationTitle = (isset($client->salutation) && !empty($client->salutation)) ? __($client->salutation) : '';
                $clientAcademicTitle = (isset($client->title) && !empty($client->title)) ? $client->title : '';
                $clientFirstName = (isset($client->first_name) && !empty($client->first_name)) ? $client->first_name : '';
                $clientLastName = (isset($client->last_name) && !empty($client->last_name)) ? $client->last_name : '';
                $clientEmail = (isset($client->email) && !empty($client->email)) ? $client->email : '';
                $clientPhone = (isset($client->phone) && !empty($client->phone)) ? $client->phone : '';
                $clientMobile = (isset($client->mobile) && !empty($client->mobile)) ? $client->mobile : '';
                $clientWebsite = (isset($client->website) && !empty($client->website)) ? $client->website : '';
                $constructionStreetN = (isset($constructionDetail->address_1) && !empty($constructionDetail->address_1)) ? $constructionDetail->address_1 : '';
                $constructionAdditionalAddress = (isset($constructionDetail->address_2) && !empty($constructionDetail->address_2)) ? $constructionDetail->address_2 : '';
                $constructionZipcode = (isset($constructionDetail->zip_code) && !empty($constructionDetail->zip_code)) ? $constructionDetail->zip_code : '';
                $constructionCity = (isset($constructionDetail->city) && !empty($constructionDetail->city)) ? $constructionDetail->city : '';
                $constructionState = (isset($constructionDetail->state) && !empty($constructionDetail->state)) ? $constructionDetail->state : '';
                $constructionCountry = (isset($constructionDetail->country) && !empty($constructionDetail->country) && (isset($constructionDetail->countryDetail) && $constructionDetail->countryDetail !=null)) ? $constructionDetail->countryDetail->name : '';
                $constructionTaxNumber = (isset($constructionDetail->tax_number) && !empty($constructionDetail->tax_number)) ? $constructionDetail->tax_number : '';
                $constructionTaxNotes = (isset($constructionDetail->notes) && !empty($constructionDetail->notes)) ? $constructionDetail->notes : '';
                if (isset($client->salutation) && $client->salutation == 'Mr.') {
                    $clientSalutation = __("Mr. salutation");
                } else if (isset($client->salutation) && $client->salutation == 'Ms.') {
                    $clientSalutation = __("Ms. salutation");
                }
                if (isset($constructionDetail->salutation) && $constructionDetail->salutation == 'Mr.') {
                    $constructionSalutation = __("Mr. salutation");
                } else if (isset($constructionDetail->salutation) && $constructionDetail->salutation == 'Ms.') {
                    $constructionSalutation = __("Ms. salutation");
                }
    
            }
    
            // Variable array 
    
            $allVariable = [
                "{client_name}", // 1
                "{estimation.title}", // 2
                "{client.company_name}", // 3
                "{client.salutation_title}", // 4
                "{client.academic_title}", // 5
                "{client.first_name}", // 6
                "{client.last_name}", // 7
                "{client.email}", // 8
                "{client.phone}", // 9
                "{client.mobile}", // 10
                "{client.website}", // 11
                "{construction.street}", // 12
                "{construction.additional_address}", // 13
                "{construction.zipcode}", // 14
                "{construction.city}", // 15
                "{construction.state}", // 16
                "{construction.country}", // 17
                "{construction.tax_number}", // 18
                "{construction.notes}", // 19
                "{current.date+21days}", // 20
                "{client.salutation}", // 21
                "{construction.salutation}", // 22
            ];
    
            $allVariabelValues = [
    
                $client_name, // 1 
                $estimation->title, // 2
                $clientCompanyName, // 3
                $clientSalutationTitle, // 4
                $clientAcademicTitle, // 5
                $clientFirstName, // 6
                $clientLastName, // 7
                $clientEmail, // 8
                $clientPhone, // 9
                $clientMobile, // 10
                $clientWebsite, // 11
                $constructionStreetN, // 12
                $constructionAdditionalAddress, // 13
                $constructionZipcode, // 14
                $constructionCity, // 15
                $constructionState, // 16
                $constructionCountry, // 17
                $constructionTaxNumber, // 18
                $constructionTaxNotes, // 19
                date("m/d/Y", strtotime("+21days")), // 20
                $clientSalutation, // 21
                $constructionSalutation, // 22
            ];
    
            // Subject Text
            $subject = $request->subject;
            $subject = str_replace($allVariable, $allVariabelValues , $subject);
            // Email Text
            $message = $request->email_text;
            $message = str_replace($allVariable, $allVariabelValues , $message);
            // Extra Notes Text
            $extra_notes = $request->extra_notes;
            $extra_notes = str_replace($allVariable, $allVariabelValues , $extra_notes);
            // PDF Top Notes Text
            $pdfTopNotes = $request->pdf_top_notes;
            $pdfTopNotes = str_replace($allVariable, $allVariabelValues , $pdfTopNotes);
            $site_money_format = site_money_format();

            $estimation_file_name = get_file_name($estimation->id);
            $file_name = $estimation_file_name . '.pdf';

            $project_files_ids = array();
            $project_images_files = '';
            if(isset($request->project_images_files) && !empty($request->project_images_files)){
                $files = '';
                $files = explode(",", $request->project_images_files);
                foreach($files as $prow){
                    $project_files_ids[] = decrypt($prow);
                }
                $project_images_files = ProjectFile::whereIn('id', $project_files_ids)->get();
            }
            
            $project_other_files = '';
            $project_other_files_ids = array();
            if(isset($request->project_other_files) && !empty($request->project_other_files)){
                $files = '';
                $files = explode(",", $request->project_other_files);
                foreach($files as $prow){
                    $project_other_files_ids[] = decrypt($prow);
                }
                $project_other_files = ProjectFile::whereIn('id', $project_other_files_ids)->get();
            }
    
            $data = ['estimation' => $estimation,
                'settings' => $settings,
				'company_details' => $company_details,
                'quote' => $quote,
                'client' => $client,
                'client_name' => $client_name,
                'client_email' => $client_email,
                'message' => $message,
                'extra_notes' => $extra_notes,
                'pdfTopNotes' => $pdfTopNotes,
                'contractor' => $contractor,
                'project'   => $project,
                'site_money_format' => $site_money_format,
                'path' => $path,
                'file_name' => $file_name,
                'project_images_files' => $project_images_files,
            ];
            if ($request->type == "pdf") {
                return $this->generatePDF($data, true); 
            }
            $dir = $this->generatePDF($data);
    
            $cc_email = $request->cc_email;
            $bcc_email = $request->bcc_email;
            if (isset($request->copy_to_company) && $request->copy_to_company == true) {
                $bcc_email[] = $company_details['company_email'];
            }
            if (isset($request->copy_to_subcontractor) && $request->copy_to_subcontractor == true) {
                if (isset($quote->subContractor->email) && ($quote->subContractor->email != '')) {
                    $bcc_email[] = $company_details['company_email'];
                }
            }

            $additional_format_files_list = array();
            if(isset($request->additional_format_files) && !empty($request->additional_format_files)){
                foreach($request->additional_format_files as $row){
                    if($row == 'gaeb'){
                        $additional_format_files_list[] = ($this->exportEstimationInGaeb(Crypt::encrypt($estimation->id), 'attach'));
                    }
                    if($row == 'excel'){
                        $additional_format_files_list[] = ($this->exportEstimationInExcel(Crypt::encrypt($estimation->id), 'attach'));
                    }
                    if($row == 'csv'){
                        $additional_format_files_list[] = ($this->exportEstimationInCSV(Crypt::encrypt($estimation->id), 'attach'));
                    }
                    if($row == 'image_zip'){
                        $additional_format_files_list[] = ($this->exportEstimationImagesInZip(Crypt::encrypt($estimation->id), 'attach', $project_images_files));
                    }
                }
            }
            $emailData = (object) [
                "subject" => $subject,
                "sender_name" => env("APP_NAME"),
                "content" => $message,
                "sender" => env("MAIL_FROM_ADDRESS"),
                "view" => 'taskly::email.common',
                'pdf' => $dir,
                'cc' => $cc_email,
                'bcc' => $bcc_email,
                'additional_files' => isset($request->additional_files) ? $request->additional_files : '',
                'project_other_files' => isset($project_other_files) ? $project_other_files : '',
                'additional_format_files_list' => isset($additional_format_files_list) ? $additional_format_files_list : '',
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
            if(isset($client->user_id)) {
                $recipient = User::find($client->user_id);
                if(isset($recipient->id)){
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
            if (!empty($additional_format_files_list)) {
                foreach ($additional_format_files_list as $row) {
                    unlink('uploads/export/' . ($row));
                }
            }
            $estimation->status = 2;
            $estimation->save();
            $response['message'] = 'Email successfully sent';
            $response['status'] = true;
    
            $fileName = "";
    
            // $get_file_name = basename($dir);
            if(File::exists($dir)){
                $dir_path = 'uploads/files/'; 
                if (!is_dir($dir_path)) {
                    mkdir($dir_path, 0777);
                }
                $fileName = $file_name;
                $new_path = $dir_path.$file_name;
                File::copy($dir, $new_path);
            }
    
            
            $new_message = "Client Estimate Quate : " . $quote->title;
            $client_message = isset($message) ? $message : $new_message;
    
            $feedback = new ProjectClientFeedback();
            $feedback->project_id = $estimation->project_id;
            $feedback->file = isset($fileName) ? $fileName : '';
            $feedback->feedback = $client_message;
            $feedback->feedback_by = Auth::user()->id;
            $feedback->save();

            $success_message = 'Email successfully sent';
            if ($request->type == "email") {
                \Session::flash('success',$success_message);
            }
        }

        return response(['status' => true, 'message' => $success_message]);
        
    }

	public function bulkDeleteProjectEstimation(Request $request)
    {
        //ProjectEstimation::find($id)->delete();
        $remove_estimation_ids = isset($request->remove_estimation_ids) ? json_decode($request->remove_estimation_ids) : array();
        $estimation_ids = array();
        if(count($remove_estimation_ids) > 0){
            foreach($remove_estimation_ids as $remove_estimation_id) {
                $estimation_ids[] = Crypt::decrypt($remove_estimation_id);;
            }
        }
        if(count($estimation_ids) > 0){
            ProjectEstimation::whereIn("id", $estimation_ids)->delete();
            return redirect()->back()->with('success', __('Estimations Deleted Successfully.'));
        } else {
            return redirect()->back()->with('error', __('Something went wrong.'));
        }
    }

	public function callAiSmartTemplate_new(Request $request) {
        if(isset($request->smart_template_id) && $request->smart_template_id > 0) {
            ini_set("max_execution_time", "-1");
            ini_set("memory_limit", "-1");
            $smart_template     = SmartTemplate::with('template_details')->where('id', $request->smart_template_id)->first();
            if (isset($smart_template->id)) {
                if (!empty($request->estimation_id)) {
                    $project_estimation = ProjectEstimation::with("estimation_products", "getProjectDetail")->find($request->estimation_id);
					$quoteItem 		= $project_estimation->estimation_products;
					$type 			= 'estimation_products';

                    if (isset($project_estimation->id)) {
						$quate_id = 0;
						if($smart_template->type == 1) {
							$quote_title 	= isset($smart_template->title) ? $smart_template->title : '';
							$old_quote 		= EstimateQuote::with("quoteItem")->where('project_estimation_id', $project_estimation->id)->where('is_ai', 1)->where('smart_template_id', $smart_template->id)->first();

							if (isset($old_quote->id)) {
								$quoteItem 			= $old_quote->quoteItem;
								$type 				= 'quote';
								$quate_id 			= $old_quote->id;
								$old_quote->title 	= $quote_title;
								$old_quote->save();
							} else {
								$new_quote = EstimateQuote::create([
									"title" 				=> $quote_title,
									"net" 					=> 0,
									"net_with_discount" 	=> 0,
									"gross" 				=> 0,
									"gross_with_discount" 	=> 0,
									"discount" 				=> 2,
									"tax" 					=> 19,
									"is_clone" 				=> 1,
									"is_ai" 				=> 1,
									"smart_template_id" 	=> $smart_template->id,
									"markup" 				=> 0,
									"project_estimation_id" => $project_estimation->id,
									"project_id" 			=> $project_estimation->project_id
								]);
								$quate_id = $new_quote->id;
							}

							if($quate_id > 0) {
								$insert_array = array();
								foreach ($quoteItem as $item) {
									$product_id 	= ($type == 'quote') ? $item->product_id : $item->id;
									$insert_item 	= array(
										"estimate_quote_id" => $quate_id,
										"product_id" 		=> $product_id,
										"base_price" 		=> ($type == 'quote') ? $item->base_price : 0,
										"price" 			=> ($type == 'quote') ? $item->price : 0,
										"total_price" 		=> ($type == 'quote') ? $item->total_price : 0,
										"smart_template_data" 	=> ($type == 'quote') ? $item->smart_template_data : NULL,
										"all_results" 			=> ($type == 'quote') ? $item->all_results : NULL,
										"created_at" 		=> date('Y-m-d h:i:s'),
										"updated_at" 		=> date('Y-m-d h:i:s')
									);
									$insert_array[$product_id] 		= $insert_item;
								}

								if (count($insert_array) > 0) {
									if($type == 'quote') {
										EstimateQuoteItem::where('estimate_quote_id', $quate_id)->delete();
									}
									EstimateQuoteItem::insert($insert_array);
								}
							}
						}

                        $item_ids               = isset($request->item_ids) ? json_decode($request->item_ids) : array();
                        $projectEstimateProduct = ProjectEstimationProduct::whereIn("id", $item_ids)->get();
                        $insert_spr_data        = array();
                        foreach ($projectEstimateProduct as $product) {
                            $allVariable = array();
                            $allVariabelValues = array();

                            if($smart_template->type == 0) {
                                $projectEstimateProduct2    = ProjectEstimationProduct::whereIn("id", $item_ids)->pluck('name')->toArray();
                                $all_items                  = '';
                                if(count($projectEstimateProduct2) > 0){
                                    $all_items = implode(',', $projectEstimateProduct2);
                                }
                                $project_description = isset($project_estimation->getProjectDetail->description) ? $project_estimation->getProjectDetail->description : '';

                                $allVariable = [
                                    '{field1}',
                                    '{Position-Nr}',
                                    '{Name}',
                                    '{Description}',
                                    '{Quantity}',
                                    '{Unit}',
									'{Technical-Description}',
                                    '{estimation.pos}',
                                    '{estimation.name}',
                                    '{estimation.description}',
                                    '{estimation.quantity}',
                                    '{estimation.unit}',
                                    '{estimation.allitems}',
                                    '{project.description}',
                                ];
            
                                $allVariabelValues = [
                                    '{field1}',
                                    $product->pos,
                                    $product->name,
                                    $product->description,
                                    $product->quantity,
                                    $product->unit,
									$project_estimation->technical_description,
                                    $product->pos,
                                    $product->name,
                                    $product->description,
                                    $product->quantity,
                                    $product->unit,
                                    $all_items,
                                    $project_description,
                                ];
                            } else {
                                $allVariable = [
                                    '{field1}',
                                    '{Position-Nr}',
                                    '{Name}',
                                    '{Description}',
                                    '{Quantity}',
                                    '{Unit}',
									'{Technical-Description}',
                                ];
                    
                                $allVariabelValues = [
                                    '{field1}',
                                    $product->pos,
                                    $product->name,
                                    $product->description,
                                    $product->quantity,
                                    $product->unit,
									$project_estimation->technical_description,
                                ];
                            }
                
                            if(isset($smart_template->template_details) && count($smart_template->template_details) > 0) {
                                foreach($smart_template->template_details as $template_detail) {
                                    $notification_template_name = isset($template_detail->prompt->name) ? $template_detail->prompt->name : '';
                                    $notification_template_slug = isset($template_detail->prompt->slug) ? $template_detail->prompt->slug : '';
                                    $prompt_title           = isset($template_detail->prompt_title) ? $template_detail->prompt_title : '';
                                    $prompt_slug            = isset($template_detail->prompt_slug) ? $template_detail->prompt_slug : '';
                                    $promptData = str_replace($allVariable,$allVariabelValues,$template_detail->prompt_desc);
                                    $promptData = str_replace(array("\r"), "", $promptData);
                                    $number_of_request = intval($smart_template->request_count);
                                    if($smart_template->type == 0) {
                                        $number_of_request = 1;
                                    }
									if($number_of_request == 0) {
                                        $number_of_request = 1;
                                    }

									$language = isset(Auth::user()->lang) ? Auth::user()->lang : 'en';

									$insert_item = array(
										"project_id" 		=> $project_estimation->project_id,
										"estimation_id" 	=> $project_estimation->id,
										"smart_template_id" => $smart_template->id,
										"smart_template_main_title" => $smart_template->title,
										"smart_template_name" 		=> $notification_template_name,
										"smart_template_slug" 		=> $notification_template_slug,
										"type" 				=> $smart_template->type,
										"product_id" 		=> $product->id,
										"prompt_id" 		=> $template_detail->prompt_id,
										"prompt" 			=> $promptData,
										"prompt_title" 		=> $prompt_title,
										"prompt_slug" 		=> $prompt_slug,
										"number_of_request" => $number_of_request,
										"outliner" 			=> $smart_template->outliner,
										"result_operation" 	=> $smart_template->result_operation,
										"language" 			=> $language,
										"ai_model_name" 	=> isset($smart_template->ai_model->model) ? $smart_template->ai_model->model : '',
										"ai_model_provider" 			=> isset($smart_template->ai_model->provider) ? $smart_template->ai_model->provider : '',
										"extraction_ai_model_name" 		=> isset($smart_template->extraction_ai_model->model) ? $smart_template->extraction_ai_model->model : '',
										"extraction_ai_model_provider" 	=> isset($smart_template->extraction_ai_model->provider) ? $smart_template->extraction_ai_model->provider : '',
										"status" 			=> 0,
										"created_at" 		=> date('Y-m-d h:i:s'),
										"updated_at" 		=> date('Y-m-d h:i:s')
									);
									if($quate_id > 0) {
										$insert_item['quote_id'] = $quate_id;
									}
									$insert_spr_data[] 		= $insert_item;
								}
							}
						}
						if (count($insert_spr_data) > 0) {
							SmartPromptQueue::where('estimation_id', $project_estimation->id)->where('smart_template_id', $smart_template->id)->whereIn('product_id', $item_ids)->delete();
							SmartPromptQueue::insert($insert_spr_data);
						}

                        // if($smart_template->type == 0){
                        //     DescriptionProcess::dispatch();
                        // } else if($smart_template->type == 1) {
                        //     //  GPTProcess::dispatch();
                        //     // GPTProcess::withChain([
                        //     //     new SaveQuote,
                        //     // ])->dispatch();
                        // }
                        

                        return response()->json(['status' => true, 'message' => __('Success!')]);
                    } else {
                        return response(['status' => false, 'message' => __("Estimation not found")]);
                    }
                } else {
                    return response(['status' => false, 'message' => __("Estimation id Required")]);
                }
            } else {
                return response(['status' => false, 'message' => __('Smart Template not found')]);
            }
        } else {
            return response(['status' => false, 'message' => __('Smart Template id Required')]);
        }
    }

	public function queuesProgress(Request $request){
        $queue_result   = SmartPromptQueue::get_record(); 

        if ($request->method() == 'POST') {
            return response()->json(['status'=>true,'data'=> $queue_result]);
        }
	    return view('taskly::queues_progress.index', compact('queue_result'));
    }

	public function replace_ai_desc(Request $request)
    {
        try {
            if (!empty($request->estimation_id)) {
                $project_estimation = ProjectEstimation::find($request->estimation_id);
                if(isset($project_estimation->id)) {
                    $item_ids = isset($request->replace_ai_desc_ids) ? json_decode($request->replace_ai_desc_ids) : array();
                    if(count($item_ids) > 0) {
                        $project_estimation_products = ProjectEstimationProduct::whereIn("id", $item_ids)->get();
                        if(count($project_estimation_products) > 0){
                            $update_data = array();
                            foreach($project_estimation_products as $item) {
                                if(isset($item->ai_description) && !empty($item->ai_description)) {
                                    $update_data[] = array(
                                        'id' => $item->id,
                                        'description' => $item->ai_description,
                                    );
                                }
                            }
                            if (count($update_data) > 0) {
                                Batch::update(new ProjectEstimationProduct, $update_data, 'id');
                            }
                        }
                    }
                    
                    return response(['status' => true, 'message' => "Replaced"]);
                } else {
                    return response(['status' => false, 'message' => "Estimation not found"]);
                }
            } else {
                return response(['status' => false, 'message' => "Estimation id Required"]);
            }
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()]);
        }
    }

	public function cancel_queue($id)
    {
        $id 	= Crypt::decrypt($id);
		$user 	= Auth::user();
        $spq 	= SmartPromptQueue::where('estimation_id',$id)->where('status', 0)->get();
		if(count($spq) > 0) {
			foreach($spq as $queue) {
				$queue->update([
					'status' => 4,
					'error_message' => __('Cancelled by') . ' : ' . $user->name,
				]);
			}
			return redirect()->back()->with('success', __('Progress cancelled successfully'));
		} else {
			return redirect()->back()->with('error', __('Something went wrong.'));
		}
    }
}
