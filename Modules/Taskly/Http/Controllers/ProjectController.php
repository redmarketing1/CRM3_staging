<?php

namespace Modules\Taskly\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Country;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\FacadesCrypt;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Services\UserService;
use Modules\Taskly\Emails\SendInvication;
use Modules\Taskly\Emails\ShareProjectToClient;
use Modules\Taskly\Entities\ActivityLog;
use Modules\Taskly\Entities\BugComment;
use Modules\Taskly\Entities\BugFile;
use Modules\Taskly\Entities\BugReport;
use Modules\Taskly\Entities\BugStage;
use Modules\Taskly\Entities\ClientProject;
use Modules\Taskly\Entities\Comment;
use Modules\Taskly\Entities\Milestone;
use Modules\Taskly\Entities\Project;
use Modules\Taskly\Entities\ProjectFile;
use Modules\Taskly\Entities\Stage;
use Modules\Taskly\Entities\SubTask;
use Modules\Taskly\Entities\Task;
use Modules\Taskly\Entities\TaskFile;
use Modules\Taskly\Entities\UserProject;
use Modules\Taskly\Entities\VenderProject;
use Modules\Taskly\Entities\ProjectEstimation;
use Modules\Taskly\Entities\EstimateQuote;
use Modules\Taskly\Entities\ProjectProgress;
use Modules\Taskly\Entities\ProjectClientFeedback;
use Modules\Taskly\Entities\ProjectComment;
use Modules\TimeTracker\Entities\TimeTracker;
use Modules\Taskly\Events\CreateBug;
use Modules\Taskly\Events\CreateMilestone;
use Modules\Taskly\Events\CreateProject;
use Modules\Taskly\Events\CreateTask;
use Modules\Taskly\Events\CreateTaskComment;
use Modules\Taskly\Events\DestroyBug;
use Modules\Taskly\Events\DestroyMilestone;
use Modules\Taskly\Events\DestroyProject;
use Modules\Taskly\Events\DestroyTask;
use Modules\Taskly\Events\DestroyTaskComment;
use Modules\Taskly\Events\UpdateBug;
use Modules\Taskly\Events\UpdateMilestone;
use Modules\Taskly\Events\UpdateProject;
use Modules\Taskly\Events\UpdateTask;
use Modules\Taskly\Events\UpdateTaskStage;
use Modules\Taskly\Events\ProjectInviteUser;
use Modules\Taskly\Events\ProjectShareToClient;
use Modules\Taskly\Events\ProjectUploadFiles;
use Modules\Taskly\Events\UpdateBugStage;
use Modules\Account\Entities\Bill;
use Modules\Documents\Entities\Document;
use Modules\Lead\Entities\Label;
use Batch;

class ProjectController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('project manage')) {
            $objUser     = Auth::user();
            $projectUser = array();
            $city        = array();
            $state       = array();

            if (Auth::user()->hasRole('client')) {
                $projects = Project::select('projects.*')->join('client_projects', 'projects.id', '=', 'client_projects.project_id')->projectonly()->where('client_projects.client_id', '=', Auth::user()->id)->where('projects.workspace', '=', getActiveWorkSpace());
            } else {
                $projects = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->projectonly()->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', getActiveWorkSpace());
            }
            if ($request->start_date) {
                $projects->where('start_date', $request->start_date);
            }
            if ($request->end_date) {
                $projects->where('end_date', $request->end_date);
            }
            $projects = $projects->get();

            foreach ($projects as $project) {
                if (isset($project->clients)) {
                    foreach ($project->clients as $user) {
                        $projectUser[] = $user;
                    }
                }

                if (isset($project->construction_detail->city) && ($project->construction_detail->city != '')) {
                    $city[] = ucfirst($project->construction_detail->city);
                }
                if (isset($project->construction_detail->state) && ($project->construction_detail->state != '')) {
                    $state[] = ucfirst($project->construction_detail->state);
                }

            }

            $filters_request['order_by'] = array('field' => 'projects.created_at', 'order' => 'DESC');
            $project_record              = Project::get_all($filters_request);
            $all_projects                = isset($project_record['records']) ? $project_record['records'] : array();

            $project_dropdown = Label::get_project_dropdowns();
            $projectmaxprice  = EstimateQuote::max('net');
            $countries        = Country::select(['id', 'name', 'iso'])->get();
            $city             = array_unique($city);
            $state            = array_unique($state);

            return view('taskly::projects.index', compact('projects', 'project_dropdown', 'all_projects', 'countries', 'city', 'state', 'projectUser', 'projectmaxprice'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function all_data(Request $request)
    {
        $search          = $request->search;
        $start           = intval($request->start);
        $length          = intval($request->length);
        $order           = $request->order;
        $filters         = isset($request->project_table_filter) ? json_decode($request->project_table_filter) : array();
        $filters_request = (array) $filters;
        $column_array    = array(
            '',
            '',
            'projects.status',
            'projects.title',
            'projects.priority',
            'projects.construction_type',
            'projects.budget',
            'projects.created_at',
            '',
            '',
        );
        $user            = Auth::user();

        if (! empty($order)) {
            $order_field = $column_array[$order[0]['column']];
            $order_value = $order[0]['dir'];
            if ($order_field != '') {
                $filters_request['order_by'] = array('field' => $order_field, 'order' => $order_value);
            }
        }

        $filters_request['start'] = $start;
        $filters_request['take']  = $length;

        $project_record = Project::get_all($filters_request);

        $filter_count = isset($project_record['filter_count']) ? $project_record['filter_count'] : 0;
        $total_count  = isset($project_record['total_count']) ? $project_record['total_count'] : 0;
        $record       = isset($project_record['records']) ? $project_record['records'] : array();

        $data = array();

        $project_dropdown = Label::get_project_dropdowns();

        $profile      = '';
        $money_format = site_money_format();
        $update_data  = array();

        foreach ($record as $key => $value) {
            $image       = '<img class="default-thumbnail" width="100%" src="' . URL('assets/images/default_thumbnail3.png') . '">';
            $latest_file = $value->project_default_file;
            
            //dd($latest_file);
            //	dd($latest_file);
            if (isset($latest_file->file_name)) {
                //    $on_error   = 'onerror="this.onerror=null; this.src="'. URL('assets/images/default_thumbnail3.png') .'";"';

                $on_error = "";

                $file_url        = get_file('uploads/projects/' . rawurlencode($latest_file->file_name));
                $ext             = pathinfo($latest_file->file_name, PATHINFO_EXTENSION);
                $supported_image = array('gif', 'jpg', 'jpeg', 'png');
                if (! in_array($ext, $supported_image)) {
                    $image = '<a class="lightbox-link fullsize-link"
                            href="' . $file_url . '" target="_blank">
                            <img class="default-thumbnail" width="100%" src="' . URL('assets/images/pdf_icon.png') . '" ' . $on_error . '></a>';
                } else {
                    // $image = '<a class="lightbox-link fullsize-link"
                    //     href="' . $file_url . '"
                    //     data-lightbox="gallery"
                    //     data-title="Project image ">
                    //     <img width="100%" src="' . $file_url . '" '.$on_error.'>
                    // </a>';
                    $image = '<a class="lightbox-link" href="' . $file_url . '" data-title="Project image " data-lightbox="gallery">
					<img width="100%" src="' . $file_url . '" ' . $on_error . '>
                    </a>';
                }
            }

            $f_name = isset($value->construction_detail->first_name) ? $value->construction_detail->first_name : '';
            $l_name = isset($value->construction_detail->last_name) ? $value->construction_detail->last_name : '';
            $name   = '<div class="media align-items-center">
					<div class="media-body">
						<a href="' . route('project.show', $value->id) . '"
						class="name mb-0 h6 text-sm project-title">' . $value->name . '</a>
					</div>
				</div>';

            $name .= '<div class="media-body titledetails">
			<span class="construction-client-name">
			<a href="' . route('users.show', Crypt::encrypt($value->construction_detail_id)) . '" class="name mb-0 h6 text-sm">' . $f_name . ' ' . $l_name .
                '</a></span>';

            if (isset($value->construction_detail->address_1) && ($value->construction_detail->address_1 != '')) {
                $name .= '<span class="construction-address">
						' . $value->construction_detail->address_1 . '
					</span>';
            }
            $name .= '</div>';

            $project_type_on_change = "changeStatus(" . $value->id . ",'status',this)";

            $status = '<select name="project_type" id="project_type" class="form-control"
			onchange="' . $project_type_on_change . '">';
            foreach ($project_dropdown['project_status'] as $project_status) {
                $selected_project_type = ($value->status == $project_status->id) ? 'selected' : '';
                $status .= '<option value="' . $project_status->id . '" ' . $selected_project_type . '>' . $project_status->name . '</option>';
            }
            $status .= '</select>';

            $selected_label_array    = isset($value->label) ? explode(',', $value->label) : array();
            $project_label_on_change = "changeStatus(" . $value->id . ",'label',this)";
            $status_label            = '<select name="label" class="form-control filter_select2" onchange="' . $project_label_on_change . '" multiple>';
            foreach ($project_dropdown['project_label'] as $project_label) {
                $selected_status_label = (in_array($project_label->id, $selected_label_array)) ? 'selected' : '';
                $status_label .= '<option value="' . $project_label->id . '" ' . $selected_status_label . ' title="' . $project_label->background_color . '" font_color="' . $project_label->font_color . '">' . $project_label->name . '</option>';
            }
            $status_label .= '</select>';

            $priority_on_change = "changeStatus(" . $value->id . ",'priority',this)";
            $priority           = '<select name="priority" class="form-control" onchange="' . $priority_on_change . '">';
            $priority .= '<option value="">-</option>';
            $value->priority    = ($value->priority > 0) ? $value->priority : env('DEFAULT_PRIORITY');
            foreach ($project_dropdown['priority'] as $priority_row) {
                $selected_priority = ($value->priority == $priority_row->id) ? 'selected' : '';
                $priority .= '<option value="' . $priority_row->id . '" ' . $selected_priority . '>' . $priority_row->name . '</option>';
            }
            $priority .= '</select>';

            $comments       = '';
            $hasDescription = ! empty(trim($value->description));
            if ($hasDescription || $value->comments->isNotEmpty()) {
                $comments = '<ul class="comments-ul">';
                foreach ($value->comments->reverse() as $comment) {
                    $comments .= '<li>' . $comment->comment . '</li>';
                }
                if ($hasDescription) {
                    $comments .= '<li>' . $value->description . '</li>';
                }
                $comments .= '</ul>';
            }

            $selected_construction_array = isset($value->construction_type) ? explode(',', $value->construction_type) : array();
            $construction_type_on_change = "changeStatus(" . $value->id . ",'construction_type',this)";
            $construction                = '<select name="construction_type" class="form-control filter_select2" onchange="' . $construction_type_on_change . '" multiple>';
            foreach ($project_dropdown['construction_type'] as $construction_type) {
                $selected_construction_type = (in_array($construction_type->id, $selected_construction_array)) ? 'selected' : '';
                $construction .= '<option value="' . $construction_type->id . '" ' . $selected_construction_type . ' title="' . $construction_type->background_color . '" font_color="' . $construction_type->font_color . '">' . $construction_type->name . '</option>';
            }
            $construction .= '</select>';

            $selected_property_type_array = isset($value->property_type) ? explode(',', $value->property_type) : array();
            $property_type_on_change      = "changeStatus(" . $value->id . ",'property_type',this)";
            $property                     = '<select name="property_type" class="form-control filter_select2" onchange="' . $property_type_on_change . '" multiple>';
            foreach ($project_dropdown['property'] as $properties) {
                $selected_property_type = (in_array($properties->id, $selected_property_type_array)) ? 'selected' : '';
                $property .= '<option value="' . $properties->id . '" ' . $selected_property_type . ' title="' . $properties->background_color . '" font_color="' . $properties->font_color . '">' . $properties->name . '</option>';
            }
            $property .= '</select>';
            $construction .= $property;

            $users = '<div class="user-group projectusers">';
            foreach ($value->users as $projectUser) {
                $attr = '';
                if (! empty($projectUser->avatar)) {
                    $attr .= 'src="' . $profile . $projectUser->avatar . '"';
                } else {
                    $attr .= 'avatar="' . $projectUser->name . '"';
                }
                $users .= '<img ' . $attr . 'class="subc" data-bs-toggle="tooltip"
				title="' . $projectUser->name . '">';
            }

            foreach ($value->clients as $projectUser) {
                $attr2 = '';
                if (! empty($projectUser->avatar)) {
                    $attr2 .= 'src="' . $profile . $projectUser->avatar . '"';
                } else {
                    $attr2 .= 'avatar="' . $projectUser->name . '"';
                }
                $users .= '<img ' . $attr2 . ' class="staffm" data-bs-toggle="tooltip"
					title="' . $projectUser->name . '">';
            }

            $users .= '</div>';

            $action = '<div class="actions">';
            if ($user->type == 'company') {
                $action .= '<div class="action-btn bg-primary ms-2">
								<a data-size="md" data-url="' . route('projects.edit', [$value->id]) . '"  class="btn btn-sm d-inline-flex align-items-center text-white " data-ajax-popup="true" data-bs-toggle="tooltip" data-title="' . __('Edit Project') . '" title="' . __('Edit Project') . '"><i class="ti ti-pencil"></i></a>
								</a>
							</div>
					<div class="action-btn bg-secondary ms-2">
                                                        <a data-size="md" data-url="' . route('project.copy', [$value->id]) . '"  class="btn btn-sm d-inline-flex align-items-center text-white " data-ajax-popup="true" data-bs-toggle="tooltip" data-title="' . __('Duplicate Project') . '" title="' . __('Duplicate') . '"><i class="ti ti-copy"></i></a>
                                                    </div>';
            }
            $action .= '<div class="action-btn bg-warning ms-2">
							<a href="' . route('projects.show', $value->id) . '" data-bs-toggle="tooltip" title="' . __('Details') . '"  data-title="' . __('Project Details') . '" class="mx-3 btn btn-sm d-inline-flex align-items-center text-white ">
								<i class="ti ti-eye"></i>
							</a>
						</div>';
            if ($user->type == 'company') {
                $action .= '<div class="action-btn bg-danger ms-2">
				<form method="POST" action="' . route('projects.destroy', $value->id) . '" accept-charset="UTF-8">
				' . method_field('DELETE') . csrf_field() . '
				<a href="#!" class="btn btn-sm   align-items-center text-white show_confirm" data-bs-toggle="tooltip" title="Delete">
						<i class="ti ti-trash"></i>
					</a>
					</form></div>';
            }
            $action .= '</div>';


            $checkbox = '
                <div class="form-check">
                    <input type="checkbox" class="select_project" value="' . Crypt::encrypt($value->id) . '">
                </div>
            ';

            $project_net = 0;
            if ($user->type == 'company') {
                if (isset($value->client_final_quote->net)) {
                    $project_net = $value->client_final_quote->net;
                } else if (isset($value->latest_final_quote->net)) {
                    $project_net = $value->latest_final_quote->net;
                }
            }

            if ($project_net == 0 && isset($value->user_latest_quote->net)) {
                $project_net = $value->user_latest_quote->net;
            }

            $row                        = array();
            $row['checkbox']            = $checkbox;
            $row['image']               = $image;
            $row['name']                = $name;
            $row['project_status']      = $value->status;
            $row['status']              = $status;
            $row['status_color']        = isset($value->status_data->background_color) ? $value->status_data->background_color : '';
            $row['status_font_color']   = isset($value->status_data->font_color) ? $value->status_data->font_color : '';
            $row['label']               = $status_label;
            $row['label_color']         = isset($value->label_data->background_color) ? $value->label_data->background_color : '';
            $row['label_font_color']    = isset($value->label_data->font_color) ? $value->label_data->font_color : '';
            $row['priority']            = $priority;
            $row['priority_color']      = isset($value->priority_data->background_color) ? $value->priority_data->background_color : '';
            $row['priority_font_color'] = isset($value->priority_data->font_color) ? $value->priority_data->font_color : '';
            $row['comments']            = $comments;
            $row['construction']        = $construction;
            $row['property']            = $property;
            $row['project_net']         = currency_format_with_sym($project_net);
            $row['date']                = company_datetime_formate($value->created_at);
            $row['users']               = '';
            $row['action']              = $action . $users;

            $data[] = $row;

            if (isset($project_net) && $project_net > 0) {
                $update_data[] = array(
                    'id'     => $value->id,
                    'budget' => $project_net,
                );
            }
        }

        if (count($update_data) > 0) {
            Batch::update(new Project, $update_data, 'id');
        }

        $response = array(
            "recordsTotal"    => $total_count,
            "recordsFiltered" => $filter_count,
            "data"            => $data,
        );

        return $response;
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('project create')) {
            if (module_is_active('CustomField')) {
                $customFields = \Modules\CustomField\Entities\CustomField::where('workspace_id', getActiveWorkSpace())->where('module', '=', 'taskly')->where('sub_module', 'projects')->get();
            } else {
                $customFields = null;
            }
            $workspace_users = User::where('created_by', creatorId())->emp()->where('workspace_id', getActiveWorkSpace())->orWhere('id', Auth::user()->id)->get();
            return view('taskly::projects.create', compact('customFields', 'workspace_users'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

        if (Auth::user()->isAbleTo('project create')) {
            $objUser          = Auth::user();
            $currentWorkspace = getActiveWorkSpace();
            $request->validate([
                'name'        => 'required',
                'description' => 'required',
            ]);
            $post                    = $request->all();
            $post['start_date']      = $post['end_date'] = date('Y-m-d');
            $post['workspace']       = $currentWorkspace;
            $post['status']          = env('PROJECT_STATUS_NEW_REQUEST');
            $post['created_by']      = $objUser->id;
            $post['copylinksetting'] = '{"member":"on","client":"on","milestone":"off","progress":"off","basic_details":"on","activity":"off","attachment":"on","bug_report":"on","task":"off","invoice":"off","timesheet":"off" ,"password_protected":"off"}';
            $userList                = [];
            if (isset($post['users_list'])) {
                $userList = $post['users_list'];
            }
            $userList[] = $objUser->email;
            $userList   = array_unique($userList);
            $objProject = Project::create($post);
            foreach ($userList as $email) {
                $permission    = 'Member';
                $registerUsers = User::where('active_workspace', $currentWorkspace)->where('email', $email)->first();
                if ($registerUsers) {
                    if ($registerUsers->id == $objUser->id) {
                        $permission = 'Owner';
                    }
                } else {
                    $arrUser                      = [];
                    $arrUser['name']              = 'No Name';
                    $arrUser['email']             = 'bohil38865@bustayes.com';
                    $password                     = \Str::random(8);
                    $arrUser['password']          = Hash::make($password);
                    $arrUser['email_verified_at'] = date('Y-m-d h:i:s');
                    $arrUser['currant_workspace'] = $objProject->workspace;
                    $registerUsers                = User::create($arrUser);
                    $registerUsers->password      = $password;

                    //Email notification
                    if (! empty(company_setting('Create User')) && company_setting('Create User') == true) {
                        $uArr       = [
                            'email'        => $email,
                            'password'     => $password,
                            'company_name' => 'No Name',
                        ];
                        $smtp_error = EmailTemplate::sendEmailTemplate('New User', [$email], $uArr);
                    }
                }
                $this->inviteUser($registerUsers, $objProject, $permission);
            }


            if (module_is_active('CustomField')) {
                \Modules\CustomField\Entities\CustomField::saveData($objProject, $request->customField);
            }

            event(new CreateProject($request, $objProject));

            return redirect()->route('projects.index')->with('success', __('Project Created Successfully!') . ((isset($smtp_error)) ? ' <br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Project $project)
    {
        if (\Auth::user()->isAbleTo('project show')) {
            $daysleft = round((((strtotime($project->end_date) - strtotime(date('Y-m-d'))) / 24) / 60) / 60);
            $user     = Auth::user();
            if ($project) {
                $chartData        = $this->getProjectChart(
                    [
                        'workspace_id' => getActiveWorkSpace(),
                        'project_id'   => $project->id,
                        'duration'     => 'week',
                    ],
                );
                $estimationStatus = ProjectEstimation::$statues;
                $statuesColor     = ProjectEstimation::$statuesColor;
                if ($user->type != "company") {
                    $project_estimation_ids = EstimateQuote::where('project_id', $project->id)->where('user_id', Auth::user()->id)->pluck('project_estimation_id')->toArray();
                    //	$project_estimation_ids2 = SubContractorEstimation::where('project_id', $id)->where('user_id', \Auth::user()->id)->pluck('project_estimation_id')->toArray();

                    //	$project_estimation_ids = array_merge($project_estimation_ids1,$project_estimation_ids2);

                    $project_estimations = ProjectEstimation::with(['all_quotes_list'])->where('project_id', $project->id)->where('init_status', 1)->whereIn("id", $project_estimation_ids)->get();

                } else {
                    $project_estimations = ProjectEstimation::with(['all_quotes_list'])->where('project_id', $project->id)->where('init_status', 1)->get();
                }
                $active_estimation = ProjectEstimation::where('project_id', $project->id)->where('is_active', 1)->first();
                $feedbacks         = ProjectClientFeedback::where('project_id', $project->id)->whereNull('parent')->get();
                $comments          = ProjectComment::where('project_id', $project->id)->whereNull('parent')->get();
            }
            $site_money_format = site_money_format();

            $project_dropdowns = Label::get_project_dropdowns();

            $projectStatus      = isset($project_dropdowns['project_status']) ? $project_dropdowns['project_status'] : array();
            $status_labels      = isset($project_dropdowns['project_label']) ? $project_dropdowns['project_label'] : array();
            $priorities         = isset($project_dropdowns['priority']) ? $project_dropdowns['priority'] : array();
            $construction_types = isset($project_dropdowns['construction_type']) ? $project_dropdowns['construction_type'] : array();
            $properties         = isset($project_dropdowns['property']) ? $project_dropdowns['property'] : array();

            //	dd($project->status_data);

            return view('taskly::projects.show', compact('project', 'daysleft', 'chartData', 'project_estimations', 'estimationStatus', 'statuesColor', 'site_money_format', 'active_estimation', 'feedbacks', 'comments', 'projectStatus', 'status_labels', 'priorities', 'construction_types', 'properties'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function getProjectChart($arrParam)
    {
        $arrDuration = [];
        if ($arrParam['duration'] && $arrParam['duration'] == 'week') {
            $previous_week = Project::getFirstSeventhWeekDay(-1);
            foreach ($previous_week['datePeriod'] as $dateObject) {
                $arrDuration[$dateObject->format('Y-m-d')] = $dateObject->format('D');
            }
        }

        $arrTask     = [
            'label' => [],
            'color' => [],
        ];
        $stages      = Stage::where('workspace_id', '=', $arrParam['workspace_id'])->orderBy('order');
        $stagesQuery = $stages->pluck('name', 'id')->toArray();
        foreach ($arrDuration as $date => $label) {
            $objProject = Task::select('status', DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->groupBy('status');
            if (isset($arrParam['project_id'])) {
                $objProject->where('project_id', '=', $arrParam['project_id']);
            }
            if (isset($arrParam['workspace_id'])) {
                $objProject->whereIn('project_id', function ($query) use ($arrParam) {
                    $query->select('id')->from('projects')->where('workspace', '=', $arrParam['workspace_id']);
                });
            }
            $data = $objProject->pluck('total', 'status')->all();
            foreach ($stagesQuery as $id => $stage) {
                $arrTask[$id][] = isset($data[$id]) ? $data[$id] : 0;
            }
            $arrTask['label'][] = __($label);
        }
        $arrTask['stages'] = $stagesQuery;
        $arrTask['color']  = $stages->pluck('color')->toArray();
        return $arrTask;
    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Project $project)
    {
       
        if (Auth::user()->isAbleTo('project edit')) {
            if (module_is_active('CustomField')) {
                $project->customField = \Modules\CustomField\Entities\CustomField::getData($project, 'taskly', 'projects');
                $customFields         = \Modules\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'taskly')->where('sub_module', 'projects')->get();
            } else {
                $customFields = null;
            }
            return view('taskly::projects.edit', compact('project', 'customFields'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Project $project)
    {
        if (Auth::user()->isAbleTo('project edit')) {
            $objUser = Auth::user();
            $project->update($request->all());

            if (module_is_active('CustomField')) {
                \Modules\CustomField\Entities\CustomField::saveData($project, $request->customField);
            }
            event(new UpdateProject($request, $project));
            return redirect()->back()->with('success', __('Project Updated Successfully!'));
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($projectID)
    {
        $objUser = Auth::user();
        $project = Project::find($projectID);

        if ($project->created_by == $objUser->id) {
            $task = Task::where('project_id', '=', $project->id)->count();
            $bug  = BugReport::where('project_id', '=', $project->id)->count();

            if ($task == 0 && $bug == 0) {
                UserProject::where('project_id', '=', $projectID)->delete();
                $ProjectFiles = ProjectFile::where('project_id', '=', $projectID)->get();
                foreach ($ProjectFiles as $ProjectFile) {

                    delete_file($ProjectFile->file_path);
                    $ProjectFile->delete();
                }

                Milestone::where('project_id', '=', $projectID)->delete();
                ActivityLog::where('project_id', '=', $projectID)->delete();

                if (module_is_active('CustomField')) {
                    $customFields = \Modules\CustomField\Entities\CustomField::where('module', 'taskly')->where('sub_module', 'projects')->get();
                    foreach ($customFields as $customField) {
                        $value = \Modules\CustomField\Entities\CustomFieldValue::where('record_id', '=', $projectID)->where('field_id', $customField->id)->first();
                        if (! empty($value)) {
                            $value->delete();
                        }
                    }
                }
                event(new DestroyProject($project));
                $project->delete();

                return redirect()->back()->with('success', __('Project Deleted Successfully!'));
            } else {
                return redirect()->back()->with('error', __('There are some Task and Bug on Project, please remove it first!'));
            }
        } else {
            return redirect()->route('projects.index')->with('error', __("You can't Delete Project!"));
        }
    }
    public function milestone($projectID)
    {
        $currentWorkspace = getActiveWorkSpace();
        $project          = Project::find($projectID);
        return view('taskly::projects.milestone', compact('currentWorkspace', 'project'));
    }

    public function milestoneStore($projectID, Request $request)
    {
        $currentWorkspace = getActiveWorkSpace();
        $project          = Project::find($projectID);
        $user1            = $currentWorkspace;
        $request->validate(
            [
                'title'   => 'required',
                'status'  => 'required',
                'cost'    => 'required',
                'summary' => 'required',
            ],
        );

        $milestone             = new Milestone();
        $milestone->project_id = $project->id;
        $milestone->title      = $request->title;
        $milestone->status     = $request->status;
        $milestone->cost       = $request->cost;
        $milestone->summary    = $request->summary;
        $milestone->save();


        ActivityLog::create(
            [
                'user_id'    => Auth::user()->id,
                'user_type'  => get_class(Auth::user()),
                'project_id' => $project->id,
                'log_type'   => 'Create Milestone',
                'remark'     => json_encode(['title' => $milestone->title]),
            ],
        );
        event(new CreateMilestone($request, $milestone));

        return redirect()->back()->with('success', __('Milestone Created Successfully!'));
    }

    public function milestoneEdit($milestoneID)
    {
        $currentWorkspace = getActiveWorkSpace();
        $milestone        = Milestone::find($milestoneID);

        return view('taskly::projects.milestoneEdit', compact('milestone', 'currentWorkspace'));
    }

    public function milestoneUpdate($milestoneID, Request $request)
    {
        $currentWorkspace = getActiveWorkSpace();
        $user1            = $currentWorkspace;

        $request->validate(
            [
                'title'  => 'required',
                'status' => 'required',
                'cost'   => 'required',
            ],
        );

        $milestone             = Milestone::find($milestoneID);
        $milestone->title      = $request->title;
        $milestone->status     = $request->status;
        $milestone->cost       = $request->cost;
        $milestone->summary    = $request->summary;
        $milestone->progress   = $request->progress;
        $milestone->start_date = $request->start_date;
        $milestone->end_date   = $request->end_date;
        $milestone->save();

        event(new UpdateMilestone($request, $milestone));

        return redirect()->back()->with('success', __('Milestone Updated Successfully!'));
    }

    public function milestoneDestroy($milestoneID)
    {
        $milestone = Milestone::find($milestoneID);

        event(new DestroyMilestone($milestone));

        $milestone->delete();

        return redirect()->back()->with('success', __('Milestone deleted Successfully!'));
    }

    public function milestoneShow($milestoneID)
    {
        $currentWorkspace = getActiveWorkSpace();
        $milestone        = Milestone::find($milestoneID);

        return view('taskly::projects.milestoneShow', compact('currentWorkspace', 'milestone'));
    }

    public function inviteUser($user, $project, $permission)
    {
        // assign workspace first

        // assign project
        $arrData               = [];
        $arrData['user_id']    = $user->id;
        $arrData['project_id'] = $project->id;
        $is_invited            = UserProject::where($arrData)->first();
        $smtp_error            = [];
        $smtp_error['status']  = true;
        $smtp_error['msg']     = '';
        if (! $is_invited) {
            UserProject::create($arrData);
            if ($permission != 'Owner') {
                if (! empty(company_setting('User Invited')) && company_setting('User Invited') == true) {
                    try {
                        $setconfing = SetConfigEmail();
                        if ($setconfing == true) {
                            try {
                                Mail::to($user->email)->send(new SendInvication($user, $project));
                            } catch (\Exception $e) {
                                $smtp_error['status'] = false;
                                $smtp_error['msg']    = $e->getMessage();
                            }
                        } else {
                            $smtp_error['status'] = false;
                            $smtp_error['msg']    = __('Something went wrong please try again ');
                        }
                    } catch (\Exception $e) {
                        $smtp_error['status'] = false;
                        $smtp_error['msg']    = $e->getMessage();
                    }
                }
            }
            return $smtp_error;
        } else {
            $smtp_error['status'] = false;
            $smtp_error['msg']    = 'User already invited.';
            return $smtp_error;
        }
    }
    public function popup($projectID)
    {
        $objUser          = Auth::user();
        $currentWorkspace = getActiveWorkSpace();
        $project          = Project::where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();
        // $workspace_users  = User::where('created_by',creatorId())->emp()->where('workspace_id',getActiveWorkSpace())->get();
        $workspace_clients = User::where('created_by', creatorId())->where('type', 'client')->where('workspace_id', getActiveWorkSpace())->get();

        $workspace_users = User::where('created_by', '=', creatorId())->emp()->where('workspace_id', getActiveWorkSpace())->whereNOTIn(
            'id',
            function ($q) use ($project) {
                $q->select('user_id')->from('user_projects')->where('project_id', '=', $project->id);
            }
        )->get();

        return view('taskly::projects.invite', compact('currentWorkspace', 'project', 'workspace_users', 'workspace_clients'));
    }

    public function invite(Request $request, $projectID)
    {
        $currentWorkspace = getActiveWorkSpace();
        $post             = $request->all();
        $userList         = $post['users_list'];

        $objProject = Project::find($projectID);

        foreach ($userList as $email) {
            $permission    = 'Member';
            $registerUsers = User::where('email', $email)->where('workspace_id', $currentWorkspace)->first();
            if ($registerUsers) {
                $user_in = $this->inviteUser($registerUsers, $objProject, $permission);
                if ($user_in['status'] != true) {
                    return redirect()->back()->with('error', $user_in['msg']);
                }
            } else {
                $arrUser                      = [];
                $arrUser['name']              = 'No Name';
                $arrUser['email']             = 'bohil38865@bustayes.com';
                $password                     = \Str::random(8);
                $arrUser['password']          = Hash::make($password);
                $arrUser['email_verified_at'] = date('Y-m-d h:i:s');
                $arrUser['currant_workspace'] = $objProject->workspace;
                $registerUsers                = User::create($arrUser);
                $registerUsers->password      = $password;

                //Email notification
                if (! empty(company_setting('Create User')) && company_setting('Create User') == true) {
                    $uArr       = [
                        'email'        => $email,
                        'password'     => $password,
                        'company_name' => 'No Name',
                    ];
                    $smtp_error = EmailTemplate::sendEmailTemplate('New User', [$email], $uArr);
                }
                $this->inviteUser($registerUsers, $objProject, $permission);
            }

            event(new ProjectInviteUser($request, $registerUsers, $objProject));

            ActivityLog::create(
                [
                    'user_id'    => Auth::user()->id,
                    'user_type'  => get_class(Auth::user()),
                    'project_id' => $objProject->id,
                    'log_type'   => 'Invite User',
                    'remark'     => json_encode(['user_id' => $registerUsers->id]),
                ],
            );
        }

        return redirect()->back()->with('success', __('Users Invited Successfully!') . ((! empty($smtp_error) && $smtp_error['is_success'] == false && ! empty($smtp_error['error'])) ? '<br> <span class="text-danger">' . $smtp_error['error'] . '</span>' : ''));
    }

    // Add Update Project Team Member
    public function addProjectTeamMember(Request $request, $id)
    {
        // Check if users were selected
        if (isset($request->users) && !empty($request->users)) {
            // Use sync() to update the relationship, which will automatically add/remove members
            $project = Project::findOrFail($id);
            $project->users()->sync($request->users);
            $count = $project->users()->count();
            return response()->json([
                'is_success' => true,
                'count' => $count,
                'message' => __('Project members updated successfully.')
            ]);
        } else {
            // If no users were selected, remove all project members
            UserProject::where('project_id', $id)->delete();
            $project = Project::findOrFail($id);
            $count = $project->users()->count();
            return response()->json([
                'is_success' => true,
                'count' => $count,
                'message' => __('All project members removed successfully.')
            ]);
        }
    }

    public function sharePopup($projectID)
    {
        $objUser          = Auth::user();
        $currentWorkspace = getActiveWorkSpace();
        $project          = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();

        $clients = User::where('created_by', '=', creatorId())->where('type', 'client')->where('workspace_id', getActiveWorkSpace())->whereNOTIn(
            'id',
            function ($q) use ($project) {
                $q->select('client_id')->from('client_projects')->where('project_id', '=', $project->id);
            }
        )->get();
        return view('taskly::projects.share', compact('currentWorkspace', 'project', 'clients'));
    }

    public function sharePopupVender($projectID)
    {
        $objUser          = Auth::user();
        $currentWorkspace = getActiveWorkSpace();
        $project          = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();

        $venders = User::where('created_by', '=', creatorId())->where('type', 'vendor')->where('workspace_id', getActiveWorkSpace())->whereNOTIn(
            'id',
            function ($q) use ($project) {
                $q->select('vender_id')->from('vender_projects')->where('project_id', '=', $project->id);
            }
        )->get();
        return view('taskly::projects.share_vender', compact('currentWorkspace', 'project', 'venders'));
    }


    public function share($projectID, Request $request)
    {
        $project = Project::find($projectID);
        foreach ($request->clients as $client_id) {
            $client = User::where('type', 'client')->where('id', $client_id)->first();

            if (ClientProject::where('client_id', '=', $client_id)->where('project_id', '=', $projectID)->count() == 0) {
                ClientProject::create(
                    [
                        'client_id'  => $client_id,
                        'project_id' => $projectID,
                        'permission' => '',
                    ],
                );
            }
            if (! empty(company_setting('Project Assigned')) && company_setting('Project Assigned') == true) {
                try {
                    $setconfing = SetConfigEmail();
                    if ($setconfing == true) {
                        try {

                            Mail::to($client->email)->send(new ShareProjectToClient($client, $project));
                        } catch (\Exception $e) {
                            $smtp_error = $e->getMessage();
                        }
                    } else {
                        $smtp_error = __('Email Not Sent!!');
                    }
                } catch (\Exception $e) {
                    $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                }
            }

            event(new ProjectShareToClient($request, $client, $project));

            ActivityLog::create(
                [
                    'user_id'    => Auth::user()->id,
                    'user_type'  => get_class(Auth::user()),
                    'project_id' => $project->id,
                    'log_type'   => 'Share with Client',
                    'remark'     => json_encode(['client_id' => $client->id]),
                ],
            );
        }

        return redirect()->back()->with('success', __('Project Share Successfully!') . ((isset($smtp_error)) ? ' <br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
    }

    public function sharePopupVenderStore($projectID, Request $request)
    {
        // dd($projectID, $request );
        $project = Project::find($projectID);
        foreach ($request->vendors as $vender_id) {
            $client = User::where('type', 'vendor')->where('id', $vender_id)->first();

            if (VenderProject::where('vender_id', '=', $vender_id)->where('project_id', '=', $projectID)->count() == 0) {
                VenderProject::create(
                    [
                        'vender_id'  => $vender_id,
                        'project_id' => $projectID,
                        'permission' => '',
                    ],
                );
            }

            // if(!empty(company_setting('Project Assigned')) && company_setting('Project Assigned')  == true)
            // {
            //     try
            //     {
            //         $setconfing =  SetConfigEmail();
            //         if($setconfing ==  true)
            //         {
            //             try{

            //                 Mail::to($client->email)->send(new ShareProjectToClient($client, $project));
            //             }
            //             catch(\Exception $e){
            //                 $smtp_error = $e->getMessage();
            //             }
            //         }
            //         else
            //         {
            //             $smtp_error = __('Email Not Sent!!');
            //         }
            //     }
            //     catch(\Exception $e)
            //     {
            //         $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            //     }
            // }

            // event(new ProjectShareToClient($request , $client , $project));

            // ActivityLog::create(
            //     [
            //         'user_id' => Auth::user()->id,
            //         'user_type' => get_class(Auth::user()),
            //         'project_id' => $project->id,
            //         'log_type' => 'Share with Vender',
            //         'remark' => json_encode(['vender_id' => $client->id]),
            //     ]
            // );

        }

        return redirect()->back()->with('success', __('Project Share Successfully!') . ((isset($smtp_error)) ? ' <br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
    }

    public function getFirstSeventhWeekDay($week = null)
    {
        $first_day = $seventh_day = null;

        if (isset($week)) {
            $first_day   = Carbon::now()->addWeeks($week)->startOfWeek();
            $seventh_day = Carbon::now()->addWeeks($week)->endOfWeek();
        }

        $dateCollection['first_day']   = $first_day;
        $dateCollection['seventh_day'] = $seventh_day;

        $period = CarbonPeriod::create($first_day, $seventh_day);

        foreach ($period as $key => $dateobj) {
            $dateCollection['datePeriod'][$key] = $dateobj;
        }

        return $dateCollection;
    }

    public function gantt($projectID, $duration = 'Week')
    {
        if (\Auth::user()->isAbleTo('sub-task manage')) {
            $objUser          = Auth::user();
            $currentWorkspace = getActiveWorkSpace();
            $is_client        = '';

            if ($objUser->hasRole('client')) {
                $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();

                $is_client = 'client.';
            } else {
                $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();
            }
            $tasks = [];

            if ($objUser->type == 'client' || $objUser->typ == 'compnay') {
                $tasksobj = Task::where('project_id', '=', $project->id)->orderBy('start_date')->get();
            } else {
                $tasksobj = Task::where('project_id', '=', $project->id)->where('assign_to', '=', $objUser->id)->orderBy('start_date')->get();
            }
            foreach ($tasksobj as $task) {
                $tmp                 = [];
                $tmp['id']           = 'task_' . $task->id;
                $tmp['name']         = $task->title;
                $tmp['start']        = $task->start_date;
                $tmp['end']          = $task->due_date;
                $tmp['custom_class'] = strtolower($task->priority);
                $tmp['progress']     = $task->subTaskPercentage();
                $tmp['extra']        = [
                    'priority' => __($task->priority),
                    'comments' => count($task->comments),
                    'duration' => Date::parse($task->start_date)->format('d M Y H:i A') . ' - ' . Date::parse($task->due_date)->format('d M Y H:i A'),
                ];
                $tasks[]             = $tmp;
            }

            return view('taskly::projects.gantt', compact('currentWorkspace', 'project', 'tasks', 'duration', 'is_client'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function ganttPost($projectID, Request $request)
    {
        $objUser          = Auth::user();
        $currentWorkspace = getActiveWorkSpace();

        if ($objUser->hasRole('client')) {
            $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();
        } else {
            $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();
        }
        if ($project) {
            $id               = trim($request->task_id, 'task_');
            $task             = Task::find($id);
            $task->start_date = $request->start;
            $task->due_date   = $request->end;
            $task->save();

            return response()->json(
                [
                    'is_success' => true,
                    'message'    => __("Time Updated"),
                ],
                200,
            );
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'message'    => __("Something is wrong."),
                ],
                400,
            );
        }
    }
    public function taskBoard($projectID)
    {
        if (\Auth::user()->isAbleTo('task manage')) {
            $currentWorkspace = getActiveWorkSpace();

            $objUser = Auth::user();
            if (Auth::user()->hasRole('client')) {
                $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();
            } else {
                $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();
            }
            $stages = $statusClass = [];
            if ($project) {
                $stages = Stage::where('workspace_id', '=', getActiveWorkSpace())->orderBy('order')->get();
                foreach ($stages as $status) {
                    $statusClass[] = 'task-list-' . str_replace(' ', '_', $status->id);

                    $task = Task::where('project_id', '=', $projectID);
                    if (! Auth::user()->hasRole('client') && ! Auth::user()->hasRole('company')) {
                        if (isset($objUser) && $objUser) {
                            $task->whereRaw("find_in_set('" . $objUser->id . "',assign_to)");
                        }
                    }
                    $task->orderBy('order');
                    $status['tasks'] = $task->where('status', '=', $status->id)->get();
                }
                return view('taskly::projects.taskboard', compact('currentWorkspace', 'project', 'stages', 'statusClass'));
            } else {
                return redirect()->back()->with('error', __('Task Note Found.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function taskCreate($projectID)
    {
        $objUser          = Auth::user();
        $currentWorkspace = getActiveWorkSpace();
        if (module_is_active('CustomField')) {
            $customFields = \Modules\CustomField\Entities\CustomField::where('workspace_id', getActiveWorkSpace())->where('module', '=', 'taskly')->where('sub_module', 'tasks')->get();
        } else {
            $customFields = null;
        }
        if ($objUser->hasRole('client')) {
            $project  = Project::select('projects.*')->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();
            $projects = Project::select('projects.*')->join('client_projects', 'client_projects.project_id', '=', 'projects.id')->where('client_projects.client_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->get();
        } else {
            $project = Project::select('projects.*')->join('user_projects', 'user_projects.project_id', '=', 'projects.id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();
            ;
            $projects = Project::select('projects.*')->join('user_projects', 'user_projects.project_id', '=', 'projects.id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->get();
        }

        $users = User::select('users.*')->join('user_projects', 'user_projects.user_id', '=', 'users.id')->where('project_id', '=', $projectID)->get();

        return view('taskly::projects.taskCreate', compact('currentWorkspace', 'customFields', 'project', 'projects', 'users'));
    }

    public function taskStore(Request $request, $projectID)
    {

        $request->validate(
            [
                'project_id' => 'required',
                'title'      => 'required',
                'priority'   => 'required',
                'assign_to'  => 'required',
                'start_date' => 'required',
                'due_date'   => 'required',
            ],
        );
        $objUser          = Auth::user();
        $currentWorkspace = getActiveWorkSpace();
        $user             = $currentWorkspace;
        $project_name     = Project::where('id', $request->project_id)->first();
        if ($objUser->hasRole('client')) {
            $project = Project::where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();
        } else {
            $project = Project::select('projects.*')->join('user_projects', 'user_projects.project_id', '=', 'projects.id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $request->project_id)->first();
        }
        if ($project) {
            $post  = $request->all();
            $stage = Stage::where('workspace_id', '=', $currentWorkspace)->orderBy('order')->first();
            if ($stage) {
                $post['milestone_id'] = ! empty($request->milestone_id) ? $request->milestone_id : 0;
                $post['status']       = $stage->id;
                $post['assign_to']    = implode(",", $request->assign_to);
                $post['workspace']    = getActiveWorkSpace();
                $task                 = Task::create($post);
                ActivityLog::create(
                    [
                        'user_id'    => Auth::user()->id,
                        'user_type'  => get_class(Auth::user()),
                        'project_id' => $projectID,
                        'log_type'   => 'Create Task',
                        'remark'     => json_encode(['title' => $task->title]),
                    ],
                );

                if (module_is_active('CustomField')) {
                    \Modules\CustomField\Entities\CustomField::saveData($task, $request->customField);
                }

                event(new CreateTask($request, $task));

                return redirect()->back()->with('success', __('Task Create Successfully!'));
            } else {
                return redirect()->back()->with('error', __('Please add stages first.'));
            }
        } else {
            return redirect()->back()->with('error', __("You can't Add Task!"));
        }
    }

    public function taskShow($projectID, $taskID)
    {
        $currentWorkspace = getActiveWorkSpace();
        $task             = Task::find($taskID);
        $objUser          = Auth::user();

        $clientID = '';
        if ($objUser->hasRole('client')) {
            $clientID = $objUser->id;
        }

        return view('taskly::projects.taskShow', compact('currentWorkspace', 'task', 'clientID'));
    }
    public function taskEdit($projectID, $taskId)
    {
        $objUser          = Auth::user();
        $currentWorkspace = getActiveWorkSpace();

        if ($objUser->hasRole('client')) {
            $project  = Project::select('projects.*')->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();
            $projects = Project::select('projects.*')->join('client_projects', 'client_projects.project_id', '=', 'projects.id')->where('client_projects.client_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->get();
        } else {
            $project = Project::select('projects.*')->join('user_projects', 'user_projects.project_id', '=', 'projects.id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();
            ;
            $projects = Project::select('projects.*')->join('user_projects', 'user_projects.project_id', '=', 'projects.id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->get();
        }
        $users = User::select('users.*')->join('user_projects', 'user_projects.user_id', '=', 'users.id')->where('project_id', '=', $projectID)->get();
        $task  = Task::find($taskId);

        $stages = Stage::where('workspace_id', '=', getActiveWorkSpace())->where('created_by', creatorId())->get();
        if (module_is_active('CustomField')) {
            $task->customField = \Modules\CustomField\Entities\CustomField::getData($task, 'taskly', 'tasks');
            $customFields      = \Modules\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'taskly')->where('sub_module', 'tasks')->get();
        } else {

            $customFields = null;
        }
        $task->assign_to = explode(",", $task->assign_to);

        return view('taskly::projects.taskEdit', compact('currentWorkspace', 'project', 'projects', 'users', 'task', 'customFields', 'stages'));
    }
    public function taskUpdate(Request $request, $projectID, $taskID)
    {
        $request->validate(
            [
                'project_id' => 'required',
                'title'      => 'required',
                'priority'   => 'required',
                'assign_to'  => 'required',
                'start_date' => 'required',
                'due_date'   => 'required',
            ],
        );
        $objUser          = Auth::user();
        $currentWorkspace = getActiveWorkSpace();

        if ($objUser->hasRole('client')) {
            $project = Project::where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();
        } else {
            $project = Project::select('projects.*')->join('user_projects', 'user_projects.project_id', '=', 'projects.id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $request->project_id)->first();
        }
        if ($project) {


            $post              = $request->all();
            $post['status']    = $request->stage_id;
            $post['assign_to'] = implode(",", $request->assign_to);
            $task              = Task::find($taskID);
            $task->update($post);

            if (module_is_active('CustomField')) {
                \Modules\CustomField\Entities\CustomField::saveData($task, $request->customField);
            }
            event(new UpdateTask($request, $task));

            return redirect()->back()->with('success', __('Task Updated Successfully!'));
        } else {
            return redirect()->back()->with('error', __("You can't Edit Task!"));
        }
    }

    public function taskOrderUpdate(Request $request, $projectID)
    {
        $currentWorkspace = getActiveWorkSpace();
        $user1            = $currentWorkspace;
        if (isset($request->sort)) {
            foreach ($request->sort as $index => $taskID) {
                $task        = Task::find($taskID);
                $task->order = $index;
                $task->save();
            }
        }

        if ($request->new_status != $request->old_status) {
            $new_status   = Stage::find($request->new_status);
            $old_status   = Stage::find($request->old_status);
            $user         = Auth::user();
            $task         = Task::find($request->id);
            $task->status = $request->new_status;
            $task->save();

            $name = $user->name;
            $id   = $user->id;

            ActivityLog::create(
                [
                    'user_id'    => $id,
                    'user_type'  => get_class($user),
                    'project_id' => $projectID,
                    'log_type'   => 'Move',
                    'remark'     => json_encode(
                        [
                            'title'      => $task->title,
                            'old_status' => $old_status->name,
                            'new_status' => $new_status->name,
                        ],
                    ),
                ],
            );

            event(new UpdateTaskStage($request, $task));

            return $task->toJson();
        }
    }
    public function commentStoreFile(Request $request, $projectID, $taskID, $clientID = '')
    {
        $currentWorkspace = getActiveWorkSpace();
        $fileName         = $taskID . time() . "_" . $request->file->getClientOriginalName();

        $upload = upload_file($request, 'file', $fileName, 'tasks', []);

        if ($upload['flag'] == 1) {
            $post['task_id']   = $taskID;
            $post['file']      = $upload['url'];
            $post['name']      = $request->file->getClientOriginalName();
            $post['extension'] = $request->file->getClientOriginalExtension();
            $post['file_size'] = $request->file->getSize();
            if ($clientID) {
                $post['created_by'] = $clientID;
                $post['user_type']  = 'Client';
            } else {
                $post['created_by'] = Auth::user()->id;
                $post['user_type']  = 'User';
            }
            $TaskFile            = TaskFile::create($post);
            $user                = $TaskFile->user;
            $TaskFile->deleteUrl = '';
            if (empty($clientID)) {
                $TaskFile->deleteUrl = route(
                    'comment.destroy.file',
                    [
                        $currentWorkspace,
                        $projectID,
                        $taskID,
                        $TaskFile->id,
                    ],
                );
            }

            return $TaskFile->toJson();
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error'      => $upload['msg'],
                ],
                401,
            );
        }
    }

    public function subTaskStore(Request $request, $projectID, $taskID, $clientID = '')
    {
        $post             = [];
        $post['task_id']  = $taskID;
        $post['name']     = $request->name;
        $post['due_date'] = $request->due_date;
        $post['status']   = 0;

        if ($clientID) {
            $post['created_by'] = $clientID;
            $post['user_type']  = 'Client';
        } else {
            $post['created_by'] = Auth::user()->id;
            $post['user_type']  = 'User';
        }
        $subtask = SubTask::create($post);
        if ($subtask->user_type == 'Client') {
            $user = $subtask->client;
        } else {
            $user = $subtask->user;
        }
        $subtask->updateUrl = route(
            'subtask.update',
            [
                $projectID,
                $subtask->id,
            ],
        );
        $subtask->deleteUrl = route(
            'subtask.destroy',
            [
                $projectID,
                $subtask->id,
            ],
        );

        return $subtask->toJson();
    }
    public function subTaskDestroy($projectID, $subtaskID)
    {
        $subtask = SubTask::find($subtaskID);
        $subtask->delete();

        return "true";
    }
    public function subTaskUpdate($projectID, $subtaskID)
    {
        $subtask         = SubTask::find($subtaskID);
        $subtask->status = (int) ! $subtask->status;
        $subtask->save();
        return $subtask->toJson();
    }


    public function commentDestroyFile(Request $request, $projectID, $taskID, $fileID)
    {

        $commentFile = TaskFile::find($fileID);
        delete_file($commentFile->file);
        $commentFile->delete();

        return "true";
    }
    public function commentStore(Request $request, $projectID, $taskID, $clientID = '')
    {
        $task             = Task::find($taskID);
        $currentWorkspace = getActiveWorkSpace();
        $post             = [];
        $post['task_id']  = $taskID;
        $post['comment']  = $request->comment;
        if ($clientID) {
            $post['created_by'] = $clientID;
            $post['user_type']  = 'Client';
        } else {
            $post['created_by'] = Auth::user()->id;
            $post['user_type']  = 'User';
        }
        $comment = Comment::create($post);
        if ($comment->user_type == 'Client') {
            $user = $comment->client;
        } else {
            $user = $comment->user;
        }

        if (empty($clientID)) {
            $comment->deleteUrl = route(
                'comment.destroy',
                [
                    $projectID,
                    $taskID,
                    $comment->id,
                ],
            );
        }


        event(new CreateTaskComment($request, $comment));

        return $comment->toJson();
    }
    public function commentDestroy(Request $request, $projectID, $taskID, $commentID)
    {

        $comment = Comment::find($commentID);

        event(new DestroyTaskComment($request, $comment));
        $comment->delete();

        return "true";
    }
    public function taskDestroy($projectID, $taskID)
    {

        $objUser = Auth::user();

        event(new DestroyTask($taskID));

        Comment::where('task_id', '=', $taskID)->delete();
        SubTask::where('task_id', '=', $taskID)->delete();
        $TaskFiles = TaskFile::where('task_id', '=', $taskID)->get();
        foreach ($TaskFiles as $TaskFile) {
            delete_file($TaskFile->file);
            $TaskFile->delete();
        }
        if (module_is_active('CustomField')) {
            $customFields = \Modules\CustomField\Entities\CustomField::where('module', 'taskly')->where('sub_module', 'tasks')->get();
            foreach ($customFields as $customField) {
                $value = \Modules\CustomField\Entities\CustomFieldValue::where('record_id', '=', $taskID)->where('field_id', $customField->id)->first();
                if (! empty($value)) {
                    $value->delete();
                }
            }
        }

        $task = Task::where('id', $taskID)->delete();
        return redirect()->back()->with('success', __('Task Deleted Successfully!'));
    }

    public function bugReport($project_id)
    {
        if (\Auth::user()->isAbleTo('bug manage')) {
            $currentWorkspace = getActiveWorkSpace();

            $objUser = Auth::user();
            if ($objUser->hasRole('client')) {
                $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $project_id)->first();
            } else {
                $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $project_id)->first();
            }

            $stages = $statusClass = [];

            $stages = BugStage::where('workspace_id', '=', $currentWorkspace)->orderBy('order')->get();

            foreach ($stages as &$status) {
                $statusClass[] = 'task-list-' . str_replace(' ', '_', $status->id);
                $bug           = BugReport::where('project_id', '=', $project_id);
                if ($objUser->type != 'client') {
                    if (! Auth::user()->hasRole('client') && ! Auth::user()->hasRole('company')) {
                        $bug->where('assign_to', '=', $objUser->id);
                    }
                }
                $bug->orderBy('order');

                $status['bugs'] = $bug->where('status', '=', $status->id)->with('user')->get();
            }
            return view('taskly::projects.bug_report', compact('currentWorkspace', 'project', 'stages', 'statusClass'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function bugReportCreate($project_id)
    {
        $objUser          = Auth::user();
        $currentWorkspace = getActiveWorkSpace();

        if (module_is_active('CustomField')) {
            $customFields = \Modules\CustomField\Entities\CustomField::where('workspace_id', getActiveWorkSpace())->where('module', '=', 'taskly')->where('sub_module', 'bugs')->get();
        } else {
            $customFields = null;
        }
        if ($objUser->hasRole('client')) {
            $project = Project::where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $project_id)->first();
        } else {
            $project = Project::select('projects.*')->join('user_projects', 'user_projects.project_id', '=', 'projects.id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $project_id)->first();
        }
        $arrStatus = BugStage::where('workspace_id', '=', $currentWorkspace)->orderBy('order')->pluck('name', 'id')->all();
        $users     = User::select('users.*')->join('user_projects', 'user_projects.user_id', '=', 'users.id')->where('project_id', '=', $project_id)->get();

        return view('taskly::projects.bug_report_create', compact('currentWorkspace', 'project', 'users', 'arrStatus', 'customFields'));
    }

    public function bugReportStore(Request $request, $project_id)
    {
        $request->validate(
            [
                'title'     => 'required',
                'priority'  => 'required',
                'assign_to' => 'required',
                'status'    => 'required',
            ],
        );
        $objUser          = Auth::user();
        $currentWorkspace = getActiveWorkSpace();

        if ($objUser->hasRole('client')) {
            $project = Project::where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $project_id)->first();
        } else {
            $project = Project::select('projects.*')->join('user_projects', 'user_projects.project_id', '=', 'projects.id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $project_id)->first();
        }

        if ($project) {
            $post               = $request->all();
            $post['project_id'] = $project_id;
            $bug                = BugReport::create($post);

            if (module_is_active('CustomField')) {
                \Modules\CustomField\Entities\CustomField::saveData($bug, $request->customField);
            }

            ActivityLog::create(
                [
                    'user_id'    => $objUser->id,
                    'user_type'  => get_class($objUser),
                    'project_id' => $project_id,
                    'log_type'   => 'Create Bug',
                    'remark'     => json_encode(['title' => $bug->title]),
                ],
            );
            event(new CreateBug($request, $bug));

            return redirect()->back()->with('success', __('Bug Create Successfully!'));
        } else {
            return redirect()->back()->with('error', __("You can't Add Bug!"));
        }
    }

    public function bugReportOrderUpdate(Request $request, $project_id)
    {
        $currentWorkspace = getActiveWorkSpace();
        if (isset($request->sort)) {
            foreach ($request->sort as $index => $taskID) {
                $bug        = BugReport::find($taskID);
                $bug->order = $index;
                $bug->save();
            }
        }
        if ($request->new_status != $request->old_status) {
            $new_status  = BugStage::find($request->new_status);
            $old_status  = BugStage::find($request->old_status);
            $user        = Auth::user();
            $bug         = BugReport::find($request->id);
            $bug->status = $request->new_status;
            $bug->save();

            $name = $user->name;
            $id   = $user->id;

            event(new UpdateBugStage($request, $bug));

            ActivityLog::create(
                [
                    'user_id'    => $id,
                    'user_type'  => get_class($user),
                    'project_id' => $project_id,
                    'log_type'   => 'Move Bug',
                    'remark'     => json_encode(
                        [
                            'title'      => $bug->title,
                            'old_status' => $old_status->name,
                            'new_status' => $new_status->name,
                        ],
                    ),
                ],
            );

            return $bug->toJson();
        }
    }

    public function bugReportEdit($project_id, $bug_id)
    {
        $objUser          = Auth::user();
        $currentWorkspace = getActiveWorkSpace();

        if ($objUser->hasRole('client')) {
            $project = Project::where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $project_id)->first();
        } else {
            $project = Project::select('projects.*')->join('user_projects', 'user_projects.project_id', '=', 'projects.id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $project_id)->first();
        }
        $users = User::select('users.*')->join('user_projects', 'user_projects.user_id', '=', 'users.id')->where('project_id', '=', $project_id)->get();
        $bug   = BugReport::find($bug_id);
        if (module_is_active('CustomField')) {
            $bug->customField = \Modules\CustomField\Entities\CustomField::getData($bug, 'taskly', 'bugs');
            $customFields     = \Modules\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'taskly')->where('sub_module', 'bugs')->get();
        } else {
            $customFields = null;
        }
        $arrStatus = BugStage::where('workspace_id', '=', $currentWorkspace)->orderBy('order')->pluck('name', 'id')->all();

        return view('taskly::projects.bug_report_edit', compact('currentWorkspace', 'project', 'users', 'bug', 'arrStatus', 'customFields'));
    }

    public function bugReportUpdate(Request $request, $project_id, $bug_id)
    {

        $request->validate(
            [
                'title'     => 'required',
                'priority'  => 'required',
                'assign_to' => 'required',
                'status'    => 'required',
            ],
        );
        $objUser          = Auth::user();
        $currentWorkspace = getActiveWorkSpace();

        if ($objUser->hasRole('client')) {
            $project = Project::where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $project_id)->first();
        } else {
            $project = Project::select('projects.*')->join('user_projects', 'user_projects.project_id', '=', 'projects.id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $project_id)->first();
        }
        if ($project) {
            $post = $request->all();
            $bug  = BugReport::find($bug_id);
            $bug->update($post);

            if (module_is_active('CustomField')) {
                \Modules\CustomField\Entities\CustomField::saveData($bug, $request->customField);
            }
            event(new UpdateBug($request, $bug));
            return redirect()->back()->with('success', __('Bug Updated Successfully!'));
        } else {
            return redirect()->back()->with('error', __("You can't Edit Bug!"));
        }
    }

    public function bugReportDestroy($project_id, $bug_id)
    {

        $objUser = Auth::user();
        BugComment::where('bug_id', '=', $bug_id)->delete();
        $bugfiles = BugFile::where('bug_id', '=', $bug_id)->get();
        foreach ($bugfiles as $bugfile) {
            delete_file($bugfile->file);
            $bugfile->delete();
        }
        if (module_is_active('CustomField')) {
            $customFields = \Modules\CustomField\Entities\CustomField::where('module', 'taskly')->where('sub_module', 'bugs')->get();
            foreach ($customFields as $customField) {
                $value = \Modules\CustomField\Entities\CustomFieldValue::where('record_id', '=', $bug_id)->where('field_id', $customField->id)->first();
                if (! empty($value)) {
                    $value->delete();
                }
            }
        }
        $bug = BugReport::where('id', $bug_id)->first();

        event(new DestroyBug($bug));

        $bug = BugReport::where('id', $bug_id)->delete();

        return redirect()->back()->with('success', __('Bug Deleted Successfully!'));
    }

    public function bugReportShow($project_id, $bug_id)
    {
        $currentWorkspace = getActiveWorkSpace();
        $bug              = BugReport::find($bug_id);
        $objUser          = Auth::user();

        $clientID = '';
        if ($objUser->hasRole('client')) {
            $clientID = $objUser->id;
        }


        return view('taskly::projects.bug_report_show', compact('currentWorkspace', 'bug', 'clientID'));
    }

    public function bugCommentStore(Request $request, $project_id, $bugID, $clientID = '')
    {
        $currentWorkspace = getActiveWorkSpace();
        $post             = [];
        $post['bug_id']   = $bugID;
        $post['comment']  = $request->comment;
        if ($clientID) {
            $post['created_by'] = $clientID;
            $post['user_type']  = 'Client';
        } else {
            $post['created_by'] = Auth::user()->id;
            $post['user_type']  = 'User';
        }
        $comment = BugComment::create($post);
        if ($comment->user_type == 'Client') {
            $user = $comment->client;
        } else {
            $user = $comment->user;
        }
        if (empty($clientID)) {
            $comment->deleteUrl = route(
                'bug.comment.destroy',
                [
                    $project_id,
                    $bugID,
                    $comment->id,
                ],
            );
        }

        return $comment->toJson();
    }

    public function bugCommentDestroy(Request $request, $project_id, $bug_id, $comment_id)
    {

        $comment = BugComment::find($comment_id);
        $comment->delete();
        return "true";
    }

    public function bugStoreFile(Request $request, $project_id, $bug_id, $clientID = '')
    {

        $currentWorkspace = getActiveWorkSpace();

        $fileName = $request->file->getClientOriginalName();
        $upload   = upload_file($request, 'file', $fileName, 'tasks');
        if ($upload['flag'] == 1) {
            $post['bug_id']    = $bug_id;
            $post['file']      = $upload['url'];
            $post['name']      = $fileName;
            $post['extension'] = "." . $request->file->getClientOriginalExtension();
            $post['file_size'] = round(($request->file->getSize() / 1024) / 1024, 2) . ' MB';

            if ($clientID) {
                $post['created_by'] = $clientID;
                $post['user_type']  = 'Client';
            } else {
                $post['created_by'] = Auth::user()->id;
                $post['user_type']  = 'User';
            }
            $TaskFile            = BugFile::create($post);
            $user                = $TaskFile->user;
            $TaskFile->deleteUrl = '';
            if (empty($clientID)) {
                $TaskFile->deleteUrl = route(
                    'bug.comment.destroy.file',
                    [
                        $project_id,
                        $bug_id,
                        $TaskFile->id,
                    ],
                );
            }

            return $TaskFile->toJson();
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error'      => $upload['msg'],
                ],
                401,
            );
        }
    }

    public function bugDestroyFile(Request $request, $project_id, $bug_id, $file_id)
    {

        $commentFile = BugFile::find($file_id);
        delete_file($commentFile->file);
        $commentFile->delete();

        return "true";
    }

    public function tracker($id)
    {
        if (Auth::user()->isAbleTo('time tracker manage')) {
            $currentWorkspace = getActiveWorkSpace();
            $treckers         = TimeTracker::where('project_id', $id)->get();

            return view('taskly::time_trackers.index', compact('currentWorkspace', 'treckers', 'id'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function fileUpload($id, Request $request)
    {
        $project   = Project::find($id);
        $file_name = $request->file->getClientOriginalName();



        $upload = upload_file($request, 'file', $file_name, 'projects', []);


        if ($upload['flag'] == 1) {
            $file                 = ProjectFile::create(
                [
                    'project_id' => $project->id,
                    'file_name'  => $file_name,
                    'file_path'  => $upload['url'],
                ],
            );
            $return               = [];
            $return['is_success'] = true;
            $return['download']   = get_file($upload['url']);
            $return['delete']     = route(
                'projects.file.delete',
                [

                    $project->id,
                    $file->id,
                ],
            );

            event(new ProjectUploadFiles($request, $upload, $project));

            ActivityLog::create(
                [
                    'user_id'    => Auth::user()->id,
                    'user_type'  => get_class(Auth::user()),
                    'project_id' => $project->id,
                    'log_type'   => 'Upload File',
                    'remark'     => json_encode(['file_name' => $file_name]),
                ],
            );
            return response()->json($return);
        } else {

            return response()->json(
                [
                    'is_success' => false,
                    'error'      => $upload['msg'],
                ],
                401,
            );
        }
    }

    public function fileDownload($id, $file_id)
    {
        $project = Project::find($id);
        $file    = ProjectFile::find($file_id);
        if ($file) {
            $filename  = $file->file_name;
            $file_path = get_base_file($file->file_path);
            return \Response::download($file_path);
        } else {
            return redirect()->back()->with('error', __('File is not exist.'));
        }
    }

    public function fileDelete($id, $file_id)
    {
        $project = Project::find($id);

        $file = ProjectFile::find($file_id);
        if ($file) {
            delete_file($file->file_path);
            $file->delete();

            return response()->json(['is_success' => true], 200);
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error'      => __('File is not exist.'),
                ],
                200,
            );
        }
    }
    public function userDelete($project_id, $user_id)
    {
        $objUser          = Auth::user();
        $currentWorkspace = getActiveWorkSpace();
        $project          = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $project_id)->first();
        if (count($project->user_tasks($user_id)) == 0) {
            UserProject::where('user_id', '=', $user_id)->where('project_id', '=', $project->id)->delete();
            return redirect()->back()->with('success', __('User Deleted Successfully!'));
        } else {
            return redirect()->back()->with('warning', __('Please Remove User From Tasks!'));
        }
    }

    public function clientDelete($project_id, $client_id)
    {

        if (Auth::user()->hasRole('company')) {
            $client = ClientProject::where('client_id', $client_id)->where('project_id', $project_id)->delete();
            return redirect()->back()->with('success', __('Client Deleted Successfully!'));
        } else {
            return redirect()->back()->with('warning', __('Please Remove Client From Tasks!'));
        }
    }


    public function vendorDelete($project_id, $vender_id)
    {
        if (Auth::user()->hasRole('company')) {
            $vendor = VenderProject::where('vender_id', $vender_id)->where('project_id', $project_id)->delete();
            return redirect()->back()->with('success', __('Vendor Deleted Successfully!'));
        } else {
            return redirect()->back()->with('warning', __('Please Remove Vendor From Tasks!'));
        }
    }


    public function grid(Request $request)
    {
        if (Auth::user()->isAbleTo('project manage')) {
            $objUser = Auth::user();
            if (Auth::user()->hasRole('client')) {
                $projects = Project::select('projects.*')->join('client_projects', 'projects.id', '=', 'client_projects.project_id')->projectonly()->where('client_projects.client_id', '=', Auth::user()->id)->where('projects.workspace', '=', getActiveWorkSpace());

            } else {
                $projects = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->projectonly()->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', getActiveWorkSpace());
            }
            if ($request->start_date) {
                $projects->where('start_date', $request->start_date);
            }
            if ($request->end_date) {
                $projects->where('end_date', $request->end_date);
            }
            $projects = $projects->get();

            $project_dropdowns = Label::get_project_dropdowns();

            $projectStatus = isset($project_dropdowns['project_status']) ? $project_dropdowns['project_status'] : array();

            return view('taskly::projects.grid', compact('projects', 'projectStatus'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function TaskList($projectID)
    {
        if (\Auth::user()->isAbleTo('task manage')) {
            $currentWorkspace = getActiveWorkSpace();

            $objUser = Auth::user();
            if (Auth::user()->hasRole('client')) {
                $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();
            } else {
                $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $projectID)->first();
            }
            $stages = $statusClass = [];

            if ($project) {
                $stages = Stage::where('workspace_id', '=', getActiveWorkSpace())->orderBy('order')->get();
                foreach ($stages as $status) {
                    $statusClass[] = 'task-list-' . str_replace(' ', '_', $status->id);
                    $task          = Task::where('project_id', '=', $projectID);
                    if (! Auth::user()->hasRole('client') && ! Auth::user()->hasRole('company')) {
                        if (isset($objUser) && $objUser) {
                            $task->whereRaw("find_in_set('" . $objUser->id . "',assign_to)");
                        }
                    }
                    $task->orderBy('order');
                    $status['tasks'] = $task->where('status', '=', $status->id)->get();
                }
                return view('taskly::projects.tasklist', compact('currentWorkspace', 'project', 'stages', 'statusClass'));
            } else {
                return redirect()->back()->with('error', 'Task Not Found.');
            }
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    public function BugList($project_id)
    {
        if (\Auth::user()->isAbleTo('bug manage')) {
            $currentWorkspace = getActiveWorkSpace();

            $objUser = Auth::user();
            if ($objUser->hasRole('client')) {
                $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $project_id)->first();
            } else {
                $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currentWorkspace)->where('projects.id', '=', $project_id)->first();
            }

            $stages = $statusClass = [];

            $stages = BugStage::where('workspace_id', '=', $currentWorkspace)->orderBy('order')->get();

            foreach ($stages as &$status) {
                $statusClass[] = 'task-list-' . str_replace(' ', '_', $status->id);
                $bug           = BugReport::where('project_id', '=', $project_id);
                if ($objUser->type != 'client') {
                    if (! Auth::user()->hasRole('client') && ! Auth::user()->hasRole('company')) {
                        $bug->where('assign_to', '=', $objUser->id);
                    }
                }
                $bug->orderBy('order');

                $status['bugs'] = $bug->where('status', '=', $status->id)->get();
            }
            return view('taskly::projects.bug_report_list', compact('currentWorkspace', 'project', 'stages', 'statusClass'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function copyproject($id)
    {
        if (Auth::user()->isAbleTo('project create')) {
            $project = Project::find($id);
            return view('taskly::projects.copy', compact('project'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function copyprojectstore(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('project create')) {
            $project = Project::find($id);

            $duplicate                     = new Project();
            $duplicate['name']             = $project->name;
            $duplicate['status']           = $project->status;
            $duplicate['image']            = $project->image;
            $duplicate['description']      = $project->description;
            $duplicate['start_date']       = $project->start_date;
            $duplicate['end_date']         = $project->end_date;
            $duplicate['is_active']        = $project->is_active;
            $duplicate['currency']         = $project->currency;
            $duplicate['project_progress'] = $project->project_progress;
            $duplicate['progress']         = $project->progress;
            $duplicate['task_progress']    = $project->task_progress;
            $duplicate['tags']             = $project->tags;
            $duplicate['estimated_hrs']    = $project->estimated_hrs;
            $duplicate['workspace']        = getActiveWorkSpace();
            $duplicate['created_by']       = creatorId();
            $duplicate->save();



            if (isset($request->user) && in_array("user", $request->user)) {
                $users = UserProject::where('project_id', $project->id)->get();
                foreach ($users as $user) {
                    $users               = new UserProject();
                    $users['user_id']    = $user->user_id;
                    $users['project_id'] = $duplicate->id;
                    $users->save();
                }
            } else {
                $objUser             = Auth::user();
                $users               = new UserProject();
                $users['user_id']    = $objUser->id;
                $users['project_id'] = $duplicate->id;
                $users->save();
            }

            if (isset($request->client) && in_array("client", $request->client)) {
                $clients = ClientProject::where('project_id', $project->id)->get();
                foreach ($clients as $client) {
                    $clients               = new ClientProject();
                    $clients['client_id']  = $client->client_id;
                    $clients['project_id'] = $duplicate->id;
                    $clients->save();
                }
            }

            if (isset($request->task) && in_array("task", $request->task)) {
                $tasks = Task::where('project_id', $project->id)->get();
                foreach ($tasks as $task) {
                    $project_task                 = new Task();
                    $project_task['title']        = $task->title;
                    $project_task['priority']     = $task->priority;
                    $project_task['project_id']   = $duplicate->id;
                    $project_task['description']  = $task->description;
                    $project_task['start_date']   = $task->start_date;
                    $project_task['due_date']     = $task->due_date;
                    $project_task['milestone_id'] = $task->milestone_id;
                    $project_task['status']       = $task->status;
                    $project_task['assign_to']    = $task->assign_to;
                    $project_task['workspace']    = getActiveWorkSpace();
                    $project_task->save();

                    if (in_array("sub_task", $request->task)) {
                        $sub_tasks = SubTask::where('task_id', $task->id)->get();
                        foreach ($sub_tasks as $sub_task) {
                            $subtask               = new SubTask();
                            $subtask['name']       = $sub_task->name;
                            $subtask['due_date']   = $sub_task->due_date;
                            $subtask['task_id']    = $project_task->id;
                            $subtask['user_type']  = $sub_task->user_type;
                            $subtask['created_by'] = $sub_task->created_by;
                            $subtask['status']     = $sub_task->status;
                            $subtask->save();
                        }
                    }
                    if (in_array("task_comment", $request->task)) {
                        $task_comments = Comment::where('task_id', $task->id)->get();
                        foreach ($task_comments as $task_comment) {
                            $comment               = new Comment();
                            $comment['comment']    = $task_comment->comment;
                            $comment['created_by'] = $task_comment->created_by;
                            $comment['task_id']    = $project_task->id;
                            $comment['user_type']  = $task_comment->user_type;
                            $comment->save();
                        }
                    }
                    if (in_array("task_files", $request->task)) {
                        $task_files = TaskFile::where('task_id', $task->id)->get();
                        foreach ($task_files as $task_file) {
                            $file               = new TaskFile();
                            $file['file']       = $task_file->file;
                            $file['name']       = $task_file->name;
                            $file['extension']  = $task_file->extension;
                            $file['file_size']  = $task_file->file_size;
                            $file['created_by'] = $task_file->created_by;
                            $file['task_id']    = $project_task->id;
                            $file['user_type']  = $task_file->user_type;
                            $file->save();
                        }
                    }
                }
            }

            if (isset($request->bug) && in_array("bug", $request->bug)) {
                $bugs = BugReport::where('project_id', $project->id)->get();
                foreach ($bugs as $bug) {
                    $project_bug                = new BugReport();
                    $project_bug['title']       = $bug->title;
                    $project_bug['priority']    = $bug->priority;
                    $project_bug['description'] = $bug->description;
                    $project_bug['assign_to']   = $bug->assign_to;
                    $project_bug['project_id']  = $duplicate->id;
                    $project_bug['status']      = $bug->status;
                    $project_bug['order']       = $bug->order;
                    $project_bug->save();

                    if (in_array("bug_comment", $request->bug)) {
                        $bug_comments = BugComment::where('bug_id', $bug->id)->get();
                        foreach ($bug_comments as $bug_comment) {
                            $bugcomment               = new BugComment();
                            $bugcomment['comment']    = $bug_comment->comment;
                            $bugcomment['created_by'] = $bug_comment->created_by;
                            $bugcomment['bug_id']     = $project_bug->id;
                            $bugcomment['user_type']  = $bug_comment->user_type;
                            $bugcomment->save();
                        }
                    }
                    if (in_array("bug_files", $request->bug)) {
                        $bug_files = BugFile::where('bug_id', $bug->id)->get();
                        foreach ($bug_files as $bug_file) {
                            $bugfile               = new BugFile();
                            $bugfile['file']       = $bug_file->file;
                            $bugfile['name']       = $bug_file->name;
                            $bugfile['extension']  = $bug_file->extension;
                            $bugfile['file_size']  = $bug_file->file_size;
                            $bugfile['created_by'] = $bug_file->created_by;
                            $bugfile['bug_id']     = $project_bug->id;
                            $bugfile['user_type']  = $bug_file->user_type;
                            $bugfile->save();
                        }
                    }
                }
            }
            if (isset($request->milestone) && in_array("milestone", $request->milestone)) {
                $milestones = Milestone::where('project_id', $project->id)->get();
                foreach ($milestones as $milestone) {
                    $post               = new Milestone();
                    $post['project_id'] = $duplicate->id;
                    $post['title']      = $milestone->title;
                    $post['status']     = $milestone->status;
                    $post['cost']       = $milestone->cost;
                    $post['summary']    = $milestone->summary;
                    $post['progress']   = $milestone->progress;
                    $post['start_date'] = $milestone->start_date;
                    $post['end_date']   = $milestone->end_date;
                    $post->save();
                }
            }
            if (isset($request->project_file) && in_array("project_file", $request->project_file)) {
                $project_files = ProjectFile::where('project_id', $project->id)->get();
                foreach ($project_files as $project_file) {
                    $ProjectFile               = new ProjectFile();
                    $ProjectFile['project_id'] = $duplicate->id;
                    $ProjectFile['file_name']  = $project_file->file_name;
                    $ProjectFile['file_path']  = $project_file->file_path;
                    $ProjectFile->save();
                }
            }
            if (isset($request->activity) && in_array('activity', $request->activity)) {
                $where_in_array = [];
                if (isset($request->milestone) && in_array("milestone", $request->milestone)) {
                    array_push($where_in_array, "Create Milestone");
                }
                if (isset($request->task) && in_array("task", $request->task)) {
                    array_push($where_in_array, "Create Task", "Move");
                }
                if (isset($request->bug) && in_array("bug", $request->bug)) {
                    array_push($where_in_array, "Create Bug", "Move Bug");
                }
                if (isset($request->client) && in_array("client", $request->client)) {
                    array_push($where_in_array, "Share with Client");
                }
                if (isset($request->user) && in_array("user", $request->user)) {
                    array_push($where_in_array, "Invite User");
                }
                if (isset($request->project_file) && in_array("project_file", $request->project_file)) {
                    array_push($where_in_array, "Upload File");
                }
                if (count($where_in_array) > 0) {
                    $activities = ActivityLog::where('project_id', $project->id)->whereIn('log_type', $where_in_array)->get();
                    foreach ($activities as $activity) {
                        $activitylog               = new ActivityLog();
                        $activitylog['user_id']    = $activity->user_id;
                        $activitylog['user_type']  = $activity->user_type;
                        $activitylog['project_id'] = $duplicate->id;
                        $activitylog['log_type']   = $activity->log_type;
                        $activitylog['remark']     = $activity->remark;
                        $activitylog->save();
                    }
                }
            }
            return redirect()->back()->with('success', 'Project Created Successfully');
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    public function fileImportExport()
    {
        if (Auth::user()->isAbleTo('project import')) {
            return view('taskly::projects.import');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function fileImport(Request $request)
    {
        if (Auth::user()->isAbleTo('project import')) {
            session_start();

            $error = '';

            $html = '';

            if ($request->file->getClientOriginalName() != '') {
                $file_array = explode(".", $request->file->getClientOriginalName());

                $extension = end($file_array);
                if ($extension == 'csv') {
                    $file_data = fopen($request->file->getRealPath(), 'r');

                    $file_header = fgetcsv($file_data);
                    $html .= '<table class="table table-bordered"><tr>';

                    for ($count = 0; $count < count($file_header); $count++) {
                        $html .= '
                                <th>
                                    <select name="set_column_data" class="form-control set_column_data" data-column_number="' . $count . '">
                                    <option value="">Set Count Data</option>
                                    <option value="name">Name</option>
                                    <option value="status">Status</option>
                                    <option value="description">Description</option>
                                    <option value="start_date">Start Date</option>
                                    <option value="end_date">End Date</option>
                                    <option value="budget">Budget</option>
                                    </select>
                                </th>
                                ';
                    }
                    $html .= '</tr>';
                    $limit = 0;
                    while (($row = fgetcsv($file_data)) !== false) {
                        $limit++;

                        $html .= '<tr>';

                        for ($count = 0; $count < count($row); $count++) {
                            $html .= '<td>' . $row[$count] . '</td>';
                        }

                        $html .= '</tr>';

                        $temp_data[] = $row;
                    }
                    $_SESSION['file_data'] = $temp_data;
                } else {
                    $error = 'Only <b>.csv</b> file allowed';
                }
            } else {

                $error = 'Please Select CSV File';
            }
            $output = array(
                'error'  => $error,
                'output' => $html,
            );

            echo json_encode($output);
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    public function fileImportModal()
    {
        if (Auth::user()->isAbleTo('project import')) {
            return view('taskly::projects.import_modal');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function projectImportdata(Request $request)
    {
        if (Auth::user()->isAbleTo('project import')) {
            session_start();
            $html      = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
            $flag      = 0;
            $html .= '<table class="table table-bordered"><tr>';
            $file_data = $_SESSION['file_data'];

            unset($_SESSION['file_data']);

            $user = Auth::user();


            foreach ($file_data as $row) {
                $projects = Project::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->Where('name', 'like', $row[$request->name])->get();

                if ($projects->isEmpty()) {

                    try {
                        $project = Project::create([
                            'name'        => $row[$request->name],
                            'status'      => $row[$request->status],
                            'description' => $row[$request->description],
                            'start_date'  => $row[$request->start_date],
                            'end_date'    => $row[$request->end_date],
                            'budget'      => $row[$request->budget],
                            'created_by'  => creatorId(),
                            'workspace'   => getActiveWorkSpace(),
                        ]);
                        UserProject::create([
                            'user_id'    => creatorId(),
                            'project_id' => $project->id,
                            'is_active'  => 1,
                        ]);
                    } catch (\Exception $e) {
                        $flag = 1;
                        $html .= '<tr>';

                        $html .= '<td>' . $row[$request->name] . '</td>';
                        $html .= '<td>' . $row[$request->status] . '</td>';
                        $html .= '<td>' . $row[$request->description] . '</td>';
                        $html .= '<td>' . $row[$request->start_date] . '</td>';
                        $html .= '<td>' . $row[$request->end_date] . '</td>';
                        $html .= '<td>' . $row[$request->budget] . '</td>';

                        $html .= '</tr>';
                    }
                } else {
                    $flag = 1;
                    $html .= '<tr>';

                    $html .= '<td>' . $row[$request->name] . '</td>';
                    $html .= '<td>' . $row[$request->status] . '</td>';
                    $html .= '<td>' . $row[$request->description] . '</td>';
                    $html .= '<td>' . $row[$request->start_date] . '</td>';
                    $html .= '<td>' . $row[$request->end_date] . '</td>';
                    $html .= '<td>' . $row[$request->budget] . '</td>';

                    $html .= '</tr>';
                }
            }

            $html .= '
                            </table>
                            <br />
                            ';
            if ($flag == 1) {

                return response()->json([
                    'html'     => true,
                    'response' => $html,
                ]);
            } else {
                return response()->json([
                    'html'     => false,
                    'response' => 'Data Imported Successfully',
                ]);
            }
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }
    public function CopylinkSetting($id)
    {
        if (Auth::user()->isAbleTo('project setting')) {
            $project = Project::find($id);
            return view('taskly::projects.copylink_setting', compact('project'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function CopylinkSettingSave(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('project setting')) {
            $data                       = [];
            $data['basic_details']      = isset($request->basic_details) ? 'on' : 'off';
            $data['member']             = isset($request->member) ? 'on' : 'off';
            $data['client']             = isset($request->client) ? 'on' : 'off';
            $data['vendor']             = isset($request->vendor) ? 'on' : 'off';
            $data['milestone']          = isset($request->milestone) ? 'on' : 'off';
            $data['activity']           = isset($request->activity) ? 'on' : 'off';
            $data['attachment']         = isset($request->attachment) ? 'on' : 'off';
            $data['bug_report']         = isset($request->bug_report) ? 'on' : 'off';
            $data['task']               = isset($request->task) ? 'on' : 'off';
            $data['invoice']            = isset($request->invoice) ? 'on' : 'off';
            $data['bill']               = isset($request->bill) ? 'on' : 'off';
            $data['documents']          = isset($request->documents) ? 'on' : 'off';
            $data['timesheet']          = isset($request->timesheet) ? 'on' : 'off';
            $data['progress']           = isset($request->progress) ? 'on' : 'off';
            $data['password_protected'] = isset($request->password_protected) ? 'on' : 'off';

            $project = Project::find($id);
            if (isset($request->password_protected) && $request->password_protected == 'on') {
                $project->password = base64_encode($request->password);
            } else {
                $project->password = null;
            }
            $project->copylinksetting = (count($data) > 0) ? json_encode($data) : null;
            $project->save();

            return redirect()->back()->with('success', __('Copy Link Setting Save Successfully!'));
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }
    public function PasswordCheck(Request $request, $id = null, $lang = null)
    {
        $id_de   = Crypt::decrypt($id);
        $project = Project::find($id_de);
        if (! empty($project->copylinksetting) && json_decode($project->copylinksetting)->password_protected == 'on') {
            if (! empty($request->password) && $request->password == base64_decode($project->password)) {
                \Session::put('checked_' . $project->id, $project->id);
                return redirect()->route('project.shared.link', [$id, $lang]);
            } else {
                return redirect()->route('project.shared.link', [$id, $lang])->with('error', __('Password is wrong! Please enter valid password'));
            }
        }
    }
    public function ProjectSharedLink($id = null, $lang = null)
    {
        try {
            $id_de = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->route('login', [$lang]);
        }
        $project      = Project::find($id_de);
        $company_id   = $project->created_by;
        $workspace_id = $project->workspace;
        $project_id   = \Session::get('checked_' . $id_de);
        if ($lang == '') {
            $lang = ! empty(company_setting('defult_language', $company_id, $workspace_id)) ? company_setting('defult_language', $company_id, $workspace_id) : 'en';
        }
        \App::setLocale($lang);

        if (! empty($project->copylinksetting) && json_decode($project->copylinksetting)->password_protected == 'on' && $project_id != $id_de) {
            return view('taskly::projects.password_check', compact('company_id', 'workspace_id', 'id', 'lang'));
        }
        if ($project) {
            $bills = [];
            if (module_is_active('Account')) {
                $bills = Bill::where('workspace', '=', getActiveWorkSpace($company_id))->where('bill_module', '=', 'taskly')->where('category_id', '=', $project->id)->get();
            }
            $documents = [];
            if (module_is_active('Documents')) {
                $documents = Document::with(['Type', 'user', 'Project'])->where('project_id', $project->id)->where('created_by', creatorId())->where('workspace_id', getActiveWorkSpace())->get();
            }

            //chartdata
            $chartData = $this->getProjectChart(
                [
                    'workspace_id' => $workspace_id,
                    'project_id'   => $project->id,
                    'duration'     => 'week',
                ],
            );
            if (date('Y-m-d') == $project->end_date || date('Y-m-d') >= $project->end_date) {
                $daysleft = 0;
            } else {
                $daysleft = round((((strtotime($project->end_date) - strtotime(date('Y-m-d'))) / 24) / 60) / 60);
            }

            $stages = $statusClass = [];

            if ($project) {
                $stages = Stage::where('workspace_id', '=', $workspace_id)->orderBy('order')->get();
                foreach ($stages as $status) {
                    $statusClass[] = 'task-list-' . str_replace(' ', '_', $status->id);
                    $task          = Task::where('project_id', '=', $id_de);
                    $task->orderBy('order');
                    $status['tasks'] = $task->where('status', '=', $status->id)->get();
                }
            }

            $bug_stages = BugStage::where('workspace_id', '=', $workspace_id)->orderBy('order')->get();
            foreach ($bug_stages as &$status) {
                $statusClass[] = 'task-list-' . str_replace(' ', '_', $status->id);
                $bug           = BugReport::where('project_id', '=', $id_de);
                $bug->orderBy('order');

                $status['bugs'] = $bug->where('status', '=', $status->id)->get();
            }
            $invoices = Invoice::where('workspace', '=', $workspace_id)->where('invoice_module', '=', 'taskly')->where('category_id', $project_id)->get();
            return view('taskly::projects.sharedlink', compact('company_id', 'workspace_id', 'project', 'id', 'lang', 'chartData', 'daysleft', 'stages', 'bug_stages', 'invoices', 'bills'));
        } else {
            return redirect()->route('project.shared.link', [$id, $lang])->with('error', __('Project not found Please check the link!'));
        }
    }
    public function ProjectLinkTaskShow($projectID, $taskID)
    {
        if ($taskID) {
            $task    = Task::find($taskID);
            $project = Project::find($projectID);
            return view('taskly::projects.linktaskShow', compact('task', 'project'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
    public function ProjectLinkbugReportShow($projectID, $bug_id)
    {
        if (! empty($projectID) && ! empty($bug_id)) {
            $project = Project::find($projectID);
            $bug     = BugReport::find($bug_id);
            return view('taskly::projects.link_bug_report_show', compact('bug', 'project'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function edit_form($project_id = "", $field = "")
    {
        $objUser = Auth::user();

        $projectID = isset($project_id) ? $project_id : 0;
        if ($projectID > 0) {
            $project    = Project::find($projectID);
            $form_field = "";
            if ($field != "") {
                $form_field = $field;
            }
            $clients = genericGetContacts();
            
            $project_estimations = ProjectEstimation::where('project_id', $projectID)->where('init_status', 1)->pluck('id');
           
            $estimation_quotes   = array();
            
            if (count($project_estimations) > 0) {
                $estimation_quotes = EstimateQuote::whereIn('project_estimation_id', $project_estimations)->get();
            }
            $countries = Country::select(['id', 'name', 'iso'])->get();

            return view('taskly::projects.change_project_details', compact('project', 'project_id', 'form_field', 'clients', 'estimation_quotes', 'countries'));
        }
    }

    public function update_details(Request $request, $project_id, $form_field = "")
    {
       
        $request['country'] = (isset($request->country) && ! empty($request->country)) ? $request->country : null;

        if (Auth::user()->type == 'company') {
            $request_data = array();
            if (isset($form_field) && $form_field != "") {
                $request_data = array(
                    $form_field => 'required',
                );
                if ($form_field == "ConstructionDetails") {
                    $request_data = array(
                        'construction_user_id' => 'required',
                    );
                }
                if ($form_field == "project_status") {
                    $request_data = array(
                        'client_estimation'         => 'required',
                        'sub_contractor_estimation' => 'required',
                    );
                }
            }
            $validator = Validator::make($request->all(), $request_data);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return response()->json([
                    'is_success' => false,
                    'message'    => $messages->first(),
                ]);
            }
            
            $projectID = isset($project_id) ? $project_id : 0;

            $project_details = Project::find($projectID);

            $success_message = "";
            $update_array    = array();
            if ($form_field == "title") {
                $update_array = array(
                    'title' => $request->title,
                );

                $success_message = "Project title updated successfully";
            } else if ($form_field == "description") {
                $update_array = array(
                    'description' => $request->description,
                );

                $success_message = "Project description updated successfully";
            } else if ($form_field == "client") {
                if ($request->client_type == 'new') {
                    $return = $this->userService->genericCreateFunc('client', 'new', null, $request->all(), true);
                    if ($return['status'] == true) {
                        $new_user = $return['user'];
                        if (isset($new_user->id) && isset($new_user->id)) {
                            $request->client_id = $new_user->id;
                        }
                    } else {
                        return response()->json([
                            'is_success' => false,
                            'message'    => $return['message'],
                        ]);
                    }
                } else {
                    $return = $this->userService->genericCreateFunc($request->client_type, $request->client_type, $request->client, $request->all(), true);
                    if ($return['status'] == false) {
                        return response()->json([
                            'is_success' => false,
                            'message'    => $return['message'],
                        ]);
                    }
                }

                $is_same_invoice_address = 0;
                if ($project_details->construction_detail_id == $request->client_id) {
                    $is_same_invoice_address = 1;
                }

                $update_array = array(
                    'client'                  => $request->client_id,
                    'is_same_invoice_address' => $is_same_invoice_address,
                );

                $success_message = "Invoice details updated successfully";
            } else if ($form_field == "project_status") {
                $update_array = array(
                    //	'client_final_quote_id' => $request->client_estimation,
                    //	'sub_contractor_final_quote_id' => $request->sub_contractor_estimation,
                    'status' => env("PROJECT_STATUS_CLIENT"),
                    // 'project_status' => env("PROJECT_STATUS_CLIENT"),
                );

                $client_quote = EstimateQuote::find($request->client_estimation);
                if (isset($client_quote->project_estimation_id)) {
                    ProjectEstimation::where('project_id', $projectID)->update(['is_active' => 0]);
                    ProjectEstimation::find($client_quote->project_estimation_id)->update(['is_active' => 1]);

                    EstimateQuote::where('project_id', $client_quote->project_id)->update([
                        "final_for_client" => 0,
                    ]);
                    $client_quote->final_for_client = 1;
                    $client_quote->save();

                    EstimateQuote::where('project_estimation_id', $client_quote->project_estimation_id)->update([
                        "is_final" => 0,
                    ]);
                    EstimateQuote::where('id', $request->client_estimation)->update([
                        "is_final" => 1,
                    ]);
                }

                $sub_contractor_quote = EstimateQuote::find($request->sub_contractor_estimation);
                if (isset($sub_contractor_quote->project_estimation_id)) {
                    EstimateQuote::where('project_id', $sub_contractor_quote->project_id)->update([
                        "final_for_sub_contractor" => 0,
                    ]);
                    $sub_contractor_quote->final_for_sub_contractor = 1;
                    $sub_contractor_quote->save();

                    // EstimateQuote::where('project_estimation_id', $sub_contractor_quote->project_estimation_id)->update([
                    // 	"is_final" => 0
                    // ]);
                    // EstimateQuote::where('id', $request->sub_contractor_estimation)->update([
                    // 	"is_final" => 1
                    // ]);
                }

                $success_message = "Project status updated successfully";
            } else if ($form_field == "ConstructionDetails") {
                $new_data = array(
                    'company_name' => $request->construction_company_name,
                    'salutation'   => $request->construction_salutation,
                    'title'        => $request->construction_title,
                    'first_name'   => $request->construction_first_name,
                    'last_name'    => $request->construction_last_name,
                    'email'        => $request->construction_email,
                    'phone'        => $request->construction_phone,
                    'mobile_no'    => $request->construction_mobile,
                    'website'      => $request->construction_company_website,
                    'address_1'    => $request->construction_address_1,
                    'address_2'    => $request->construction_address_2,
                    'city'         => $request->construction_city,
                    'district_1'   => $request->construction_district_1,
                    'district_2'   => $request->construction_district_2,
                    'state'        => $request->construction_state,
                    'country'      => $request->construction_country,
                    'zip_code'     => $request->construction_zip_code,
                    'tax_number'   => $request->construction_company_tax_number,
                    'notes'        => $request->construction_company_notes,
                    'latitude'     => $request->construction_latitude,
                    'longitude'    => $request->construction_longitude,
                );
                $user_id  = 0;
                
                if (isset($request->client_type1) && $request->client_type1 == 'new') {
                    $return = $this->userService->genericCreateFunc('client', 'new', null, $new_data, true);
                    if ($return['status'] == true) {
                        $new_user = $return['user'];
                        if (isset($new_user->id)) {
                            $user_id = $new_user->id;
                        }
                    } else {
                        return response()->json([
                            'is_success' => false,
                            'message'    => $return['message'],
                        ]);
                    }
                } else {
                    $return = $this->userService->genericCreateFunc($request->client_type1, $request->client_type1, $request->construction_user_id, $new_data, true);
                    if ($return['status'] == true) {
                        $user_id = $request->construction_user_id;
                    } else {
                        return response()->json([
                            'is_success' => false,
                            'message'    => $return['message'],
                        ]);
                    }

                }

                $is_same_invoice_address = 0;
                // if ($user_id == $request->client_id) {
                if (isset($request->same_invoice_address) || $user_id == $request->client_id) {
                    $is_same_invoice_address = 1;
                }

                $client_id = 0;
                if ($project_details->client == 0 || $is_same_invoice_address == 1) {
                    $client_id = $user_id;
                }

                if (! isset($request->same_invoice_address)) {
                    if (isset($request->client_type) && $request->client_type == 'new') {
                        $return = $this->userService->genericCreateFunc('client', 'new', null, $request->all(), true);

                        if ($return['status'] == true) {
                            $new_client = $return['user'];
                            if (isset($new_client->id)) {
                                $client_id = $new_client->id;
                            }
                        } else {
                            return response()->json([
                                'is_success' => false,
                                'message'    => $return['message'],
                            ]);
                        }
                    } else {
                        $return = $this->userService->genericCreateFunc($request->client_type, $request->client_type, $request->client, $request->all(), true);

                        if ($return['status'] == true) {
                            $client_id = $request->client;
                        } else {
                            return response()->json([
                                'is_success' => false,
                                'message'    => $return['message'],
                            ]);
                        }

                    }
                }

                $update_array = array(
                    'construction_detail_id'  => $user_id,
                    'is_same_invoice_address' => $is_same_invoice_address,
                    'client'                  => $client_id,
                );

                $success_message = "Construction details updated successfully";
            } else if ($form_field == "technical_description") {
                $update_array    = array(
                    'technical_description' => $request->technical_description,
                );
                $success_message = __("Project technical description updated successfully.");
            }

            try {
                Project::find($projectID)->update($update_array);
            } catch (\Exception $e) {
                return response()->json([
                    'is_success' => false,
                    'message'    => $e->getMessage(),
                ]);
            }
            $project_details = Project::find($projectID);
            $client_data     = "";
            if (isset($project_details->client_data)) {
                $client_data = $project_details->client_data;
            }
            $construction_detail = "";
            if (isset($project_details->construction_detail)) {
                $construction_detail = $project_details->construction_detail;
            }
            $user_data = "";
            if (isset($client_data->user)) {
                $user_data = $client_data->user;
            }
            if (isset($client_data->countryDetail->name) && ($client_data->countryDetail->name != '')) {
                $client_data->country_name = $client_data->countryDetail->name;
            }
            if (isset($construction_detail->countryDetail->name) && ($construction_detail->countryDetail->name != '')) {
                $construction_detail->country_name = $construction_detail->countryDetail->name;
            }
            $status_changed = 0;
            if ($form_field == "project_status") {
                $status_changed = 1;
            }
            return response()->json([
                'is_success'          => true,
                'message'             => $success_message,
                'project'             => $project_details,
                'client_data'         => $client_data,
                'construction_detail' => $construction_detail,
                'user_details'        => $user_data,
                'status_changed'      => $status_changed,
            ]);
        } else {
            return response()->json([
                'is_success' => false,
                'message'    => __('Permission denied.'),
            ]);
        }
    }

    public function get_all_address($projectID, Request $request)
    {
        if (isset($projectID) && ! empty($projectID)) {
            $project = Project::find($projectID);
            $user    = Auth::user();
            if (isset($project->id)) {
                $html_data = view('taskly::projects.all_address', compact('project', 'user'))->render();
                return response(['status' => true, 'html_data' => $html_data]);
            } else {
                return response(['status' => false, 'message' => __('Something went wrong.')]);
            }
        } else {
            return response(['status' => false, 'message' => __('Project id not found.')]);
        }
    }

    public function project_progress($project_id = "")
    {
        $objUser   = Auth::user();
        $projectID = isset($project_id) ? Crypt::decrypt($project_id) : 0;
        if ($projectID > 0) {
            $project           = Project::find($projectID);
            $active_estimation = ProjectEstimation::where('project_id', $projectID)->where('is_active', 1)->first();
            $site_money_format = site_money_format();

            return view('taskly::projects.project_progress', compact('project', 'project_id', 'site_money_format', 'active_estimation'));
        }
    }

    public function project_map(Request $request)
    {
        if (Auth::user()->isAbleTo('project manage')) {
            $objUser = Auth::user();
            if (Auth::user()->hasRole('client')) {
                $projects = Project::select('projects.*')->join('client_projects', 'projects.id', '=', 'client_projects.project_id')->projectonly()->where('client_projects.client_id', '=', Auth::user()->id)->where('projects.workspace', '=', getActiveWorkSpace());
            } else {
                $projects = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->projectonly()->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', getActiveWorkSpace());
            }
            if ($request->start_date) {
                $projects->where('start_date', $request->start_date);
            }
            if ($request->end_date) {
                $projects->where('end_date', $request->end_date);
            }
            $projects = $projects->get();

            $locations             = array();
            $invoice_locations     = array();
            $location_construction = array();
            $location_invoice      = array();

            if (isset($projects) && count($projects) > 0) {
                foreach ($projects as $value) {

                    $status_color = isset($value->status_data->background_color) ? $value->status_data->background_color : NULL;

                    if (isset($value->construction_detail->lat) && isset($value->construction_detail->long)) {
                        $address = '<div class="address-class">
						<span>' . $value->construction_detail->address_1 . '</span>';
                        if (isset($value->construction_detail->zipcode) && ! empty($value->construction_detail->zipcode)) {
                            $address .= '<span class="clas-zip">' . $value->construction_detail->zipcode . '</span>
							<span class="clas-city">';
                            if (! empty($value->construction_detail->city)) {
                                $address .= $value->construction_detail->city;
                            }
                            $address .= '</span>';
                        }

                        if (! empty($value->construction_detail->countryDetail) && isset($value->construction_detail->countryDetail)) {
                            $address .= '<span>';
                            if (! empty($value->construction_detail->countryDetail)) {
                                $address .= $value->construction_detail->countryDetail->name;
                            }
                            $address .= '</span>';
                        }
                        $address .= `</div>`;

                        $locate['lat']          = floatval($value->construction_detail->lat);
                        $locate['lng']          = floatval($value->construction_detail->long);
                        $locate['project_name'] = '<a href="' . route('projects.show', $value->id) . '" target="_blank">' . $value->name . '</a>';
                        $locate['address_type'] = 'construction';
                        $locate['map_icon']     = 'C';
                        $locate['title']        = '';
                        $locate['content']      = '<div class="construction-detail">';

                        $group1 = '';

                        if (! empty($value->construction_detail->company_name)) {
                            $group1 .= '<span class="construction-company-name">' . $value->construction_detail->company_name . '</span> ';
                        }
                        if (! empty($value->construction_detail->first_name) || ! empty($value->construction_detail->last_name)) {
                            $group1 .= '<span class="construction-name">' . $value->construction_detail->first_name . ' ' . $value->construction_detail->last_name . '</span> ';
                        }
                        if ($group1) {
                            $locate['content'] .= '<div class="group separator">' . $group1 . '</div>';
                        } else {
                            $locate['content'] .= '<div class="group">' . $group1 . '</div>';
                        }

                        $group2 = '';
                        if (! empty($value->construction_detail->address_1)) {
                            $group2 .= '<span class="construction-address">' . $value->construction_detail->address_1;
                            if (! empty($value->construction_detail->address_2)) {
                                $group2 .= ', ' . $value->construction_detail->address_2;
                            }
                            $group2 .= '</span> ';
                        }
                        if ($group2) {
                            $locate['content'] .= '<div class="group separator">' . $group2 . '</div>';
                        } else {
                            $locate['content'] .= '<div class="group">' . $group2 . '</div>';
                        }

                        $group3 = '';
                        if (! empty($value->construction_detail->email)) {
                            $group3 .= '<span class="construction-email">' . $value->construction_detail->email . '</span> ';
                        }
                        if (! empty($value->construction_detail->phone)) {
                            $group3 .= '<span class="construction-phone">' . $value->construction_detail->phone . '</span> ';
                        }
                        if (! empty($value->construction_detail->mobile)) {
                            $group3 .= '<span class="construction-mobile">' . $value->construction_detail->mobile . '</span> ';
                        }
                        if ($group3) {
                            $locate['content'] .= '<div class="group separator">' . $group3 . '</div>';
                        } else {
                            $locate['content'] .= '<div class="group">' . $group3 . '</div>';
                        }

                        $group4 = '';
                        if (! empty($value->construction_detail->tax_number)) {
                            $group4 .= '<span class="construction-tax-number">' . $value->construction_detail->tax_number . '</span> ';
                        }
                        if (! empty($value->construction_detail->website)) {
                            $group4 .= '<span class="construction-website">' . $value->construction_detail->website . '</span> ';
                        }
                        if (! empty($value->construction_detail->notes)) {
                            $group4 .= '<span class="construction-notes">' . $value->construction_detail->notes . '</span> ';
                        }
                        if ($group4) {
                            $locate['content'] .= '<div class="group separator">' . $group4 . '</div>';
                        } else {
                            $locate['content'] .= '<div class="group">' . $group4 . '</div>';
                        }

                        $locate['content'] .= '</div>';



                        $locate['color']                  = isset($status_color) ? $status_color : '#fff';
                        $locate['construction_detail_id'] = $value->construction_detail->id;
                        if (! in_array($value->construction_detail->id, $location_construction)) {
                            array_push($location_construction, $value->construction_detail->id);
                            $locations[] = $locate;
                        }
                    }

                    if (isset($value->client_data->lat) && isset($value->client_data->long)) {
                        $address = '<div class="address-class" style="margin-bottom: 20px">
						<span>' . $value->client_data->address_1 . '</span>';
                        if (isset($value->client_data->zip_code) && ! empty($value->client_data->zip_code)) {
                            $address .= '<span class="clas-zip">' . $value->client_data->zip_code . '</span>
							<span class="clas-city">';
                            if (! empty($value->client_data->city)) {
                                $address .= $value->client_data->city;
                            }
                            $address .= '</span>';
                        }

                        if (! empty($value->client_data->state) || ! empty($value->client_data->country)) {
                            $address .= '<span>';
                            if (! empty($value->client_data->state)) {
                                $address .= $value->client_data->state;
                            }
                            if (! empty($value->client_data->countryDetail)) {
                                $address .= $value->client_data->countryDetail->name;
                            }
                            $address .= '</span>';
                        }

                        $address .= '</div>';

                        $ilocate['lat']          = floatval($value->client_data->lat);
                        $ilocate['lng']          = floatval($value->client_data->long);
                        $ilocate['project_name'] = $value->name;
                        $ilocate['address_type'] = 'invoice';
                        $ilocate['map_icon']     = 'I';
                        $ilocate['title']        = 'Invoice Address';
                        $ilocate['content']      = $address;
                        $ilocate['color']        = isset($status_color) ? $status_color : '#fff';
                        $ilocate['client_id']    = $value->client_data->id;
                        if (! in_array($value->client_data->id, $location_invoice)) {
                            array_push($location_invoice, $value->client_data->id);
                            $invoice_locations[] = $ilocate;
                        }
                    }
                }
            }
            $all_locations    = array_merge($locations, $invoice_locations);
            $project_location = $all_locations;

            return view('taskly::projects.map', compact('project_location'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function projectClientFeedbackDestroyAttachment($project_id, $id)
    {
        $project_client_feedback = ProjectClientFeedback::find($id);

        //storage limit
        $file_path                     = 'uploads/files/' . $project_client_feedback->file;
        $project_client_feedback->file = NULL;
        $project_client_feedback->save();

        delete_file($file_path);
        return redirect()->back()->with('success', __('Feedback attachment successfully deleted.'));
    }

    public function getProjectClientFeedback(Request $request)
    {
        if (isset($request->feedback_id) && ! empty($request->feedback_id)) {
            $project_client_feedback = ProjectClientFeedback::find($request->feedback_id);
            if (! empty($project_client_feedback)) {
                $is_image  = '';
                $is_image  = is_image($project_client_feedback->file);
                $link      = '';
                $file_path = 'uploads/files/' . $project_client_feedback->file;
                if (File::exists($file_path)) {
                    if ($is_image) {
                        $link = '<a class="lightbox-link" href="' . get_file('uploads/files/' . rawurlencode($project_client_feedback->file)) . '" data-lightbox="gallery" data-title="Image placeholder"><img alt="Image placeholder" src="' . get_file('uploads/files/' . rawurlencode($project_client_feedback->file)) . '" class="img-thumbnail" height="100" width="150"></a>';
                    } else {
                        $link = '<a href="' . get_file("uploads/files/" . rawurlencode($project_client_feedback->file)) . '" target="_blank">' . $project_client_feedback->file . '</a>';
                    }
                }
                return response()->json(['status' => true, 'message' => __('Client feedback found.'), 'data' => $project_client_feedback, 'file_link' => $link]);
            } else {
                return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
            }
        } else {
            return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
        }
    }

    public function projectClientFeedbackDelete(Request $request, $project_id)
    {
        if (isset($request->feedback_id) && ! empty($request->feedback_id)) {
            $project_client_feedback       = ProjectClientFeedback::find($request->feedback_id);
            $file_path                     = 'uploads/files/' . $project_client_feedback->file;
            $project_client_feedback->file = NULL;
            $project_client_feedback->save();

            delete_file($file_path);
            if (ProjectClientFeedback::where('project_id', $project_id)->where('id', $request->feedback_id)->delete()) {
                return response()->json(['status' => true, 'message' => __('Client message deleted successfully.')]);
            } else {
                return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
            }
        } else {
            return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
        }
    }

    public function getProjectComments(Request $request)
    {
        if (isset($request->comment_id) && ! empty($request->comment_id)) {
            $project_comment = ProjectComment::find($request->comment_id);
            if (! empty($project_comment)) {
                $is_image  = '';
                $is_image  = is_image($project_comment->file);
                $link      = '';
                $file_path = storage_path('uploads/files/') . $project_comment->file;
                if (File::exists($file_path)) {
                    if ($is_image) {
                        $link = '<a class="lightbox-link" href="' . get_file('uploads/files/' . rawurlencode($project_comment->file)) . '" data-lightbox="gallery" data-title="Image placeholder"><img alt="Image placeholder" src="' . get_file('uploads/files/' . rawurlencode($project_comment->file)) . '" class="img-thumbnail" height="100" width="150"></a>';
                    } else {
                        $link = '<a href="' . get_file("uploads/files/" . rawurlencode($project_comment->file)) . '" target="_blank">' . $project_comment->file . '</a>';
                    }
                }
                return response()->json(['status' => true, 'message' => __('Comment found.'), 'data' => $project_comment, 'file_link' => $link]);
            } else {
                return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
            }
        } else {
            return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
        }
    }

    public function projectCommentDestroyAttachment($project_id, $id)
    {
        $project_comment = ProjectComment::find($id);

        //storage limit
        $file_path             = 'uploads/files/' . $project_comment->file;
        $project_comment->file = NULL;
        $project_comment->save();

        delete_file($file_path);
        return redirect()->back()->with('success', __('Comment attachment successfully deleted.'));
    }

    public function projectCommentDelete(Request $request, $project_id)
    {
        if (isset($request->comment_id) && ! empty($request->comment_id)) {
            $project_comment       = ProjectComment::find($request->comment_id);
            $file_path             = 'uploads/files/' . $project_comment->file;
            $project_comment->file = NULL;
            $project_comment->save();

            delete_file($file_path);
            if (ProjectComment::where('project_id', $project_id)->where('id', $request->comment_id)->delete()) {
                return response()->json(['status' => true, 'message' => __('Comment deleted successfully.')]);
            } else {
                return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
            }
        } else {
            return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
        }
    }

    public function status_data(Request $request, $project_id)
    {
        if (Auth::user()->isAbleTo('project manage')) {
            $update_array[$request->field] = $request->field_value;

            try {
                Project::find($project_id)->update($update_array);
            } catch (\Exception $e) {
                return response()->json([
                    'is_success' => false,
                    'message'    => $e->getMessage(),
                ]);
            }

            $success_message = __($request->field . ' updated successfully');
            if ($request->field == 'label') {
                $success_message = 'Project label updated successfully';
            } else if ($request->field == 'priority') {
                $success_message = 'Project priority updated successfully';
            } else if ($request->field == 'construction_type') {
                $success_message = 'Construction type updated successfully';
            } else if ($request->field == 'property_type') {
                $success_message = 'Property type updated successfully';
            }

            return response()->json([
                'is_success' => true,
                'message'    => $success_message,
            ]);
        } else {
            return response()->json([
                'is_success' => false,
                'message'    => __('Permission denied.'),
            ]);
        }
    }

    public function changeStatus(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('project setting')) {
            $status = Project::find($id);
            if ($request->status == env("PROJECT_STATUS_NEW_REQUEST")) {
                // $status->client_final_quote_id = NULL;
                // $status->sub_contractor_final_quote_id = NULL;
                EstimateQuote::where('project_id', $id)->update([
                    "final_for_client"         => 0,
                    "final_for_sub_contractor" => 0,
                ]);
            }
            $status->status = $request->status;
            // $status->project_status = $request->status;
            $status->save();

            // if ($request->status == env("PROJECT_STATUS_FINISHED")) {
            //     $client = User::find($status->client);
            //     $user = Auth::user();
            //     $projectArr = [
            //         'project_name' => $status->title,
            //         'project_category' => !empty(Category::find($status->category)) ? Category::find($status->category)->name : '',
            //         'project_price' => $user->priceFormat($status->price),
            //         'project_client' => $client->name,
            //         'project_start_date' => $user->dateFormat($status->start_date),
            //         'project_due_date' => $user->dateFormat($status->due_date),
            //         'project_lead' => !empty(Lead::find(!empty($request->lead) ? $request->lead : 0)) ? Lead::find(!empty($request->lead) ? $request->lead->subject : 0) : '',
            //     ];
            //     // Send Email
            //     $resp = Utility::sendEmailTemplate('project_finished', [$client->id => $client->email], $projectArr);


            //     //webhook
            //     $module = "New status";
            //     $webhook = Utility::webhookSetting($module);
            //     if ($webhook) {
            //         $parameter = json_encode($status);

            //         // 1 parameter is URL , 2  (status Data) parameter is data , 3 parameter is method
            //         $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
            //         if ($status == true) {
            //             return redirect()->back()->with('success', __('Status Successfully Created.'));
            //         } else {
            //             return redirect()->back()->with('error', __('Status Call Failed.'));
            //         }
            //     }
            //     //end webhook
            //     return redirect()->back()->with('success', __('Project status successfully change.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            // }


            return redirect()->back()->with('success', __('Project status successfully change.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function changeCustomStatus(Request $request)
    {
        if (Auth::user()->isAbleTo('project setting')) {
            $status = $request->status;
            $column = trim($request->column);
            if (in_array($column, array('property_type', 'construction_type'))) {
                $status = implode(',', $request->status);
            }
            Project::find($request->project_id)->update([
                $column => $status,
            ]);
            return response()->json(['status' => true, 'message' => __('Project {$status} successfully change.')]);
        } else {
            return response()->json(['status' => false, 'message' => __('Permission denied.')]);
        }
    }

    public function bulk_action_projects(Request $request)
    {
        $type                 = isset($request->type) ? $request->type : array();
        $selected_project_ids = isset($request->project_ids) ? $request->project_ids : array();

        $project_ids = array();
        if (count($selected_project_ids) > 0) {
            foreach ($selected_project_ids as $select_project_id) {
                $project_ids[] = \Crypt::decrypt($select_project_id);
                ;
            }
        }

        if (count($project_ids) > 0 && in_array($type, array('archieve', 'delete', 'remove_archieve'))) {
            $success_message = "Project updated successfully";
            if ($type == 'archieve') {
                Project::whereIn("id", $project_ids)->update([
                    'is_archive' => 1,
                ]);

                $success_message = "Project archieved successfully";
            } else if ($type == 'remove_archieve') {
                Project::whereIn("id", $project_ids)->update([
                    'is_archive' => 0,
                ]);

                $success_message = "Project removed from archieve successfully";
            } else if ($type == 'delete') {
                $this->delete_projects($project_ids);

                $success_message = "Project deleted successfully";
            }

            return response()->json([
                'is_success' => true,
                'message'    => $success_message,
            ]);
        } else {
            return response()->json([
                'is_success' => false,
                'message'    => __('Permission denied.'),
            ]);
        }
    }

    private function delete_projects($project_ids = array())
    {
        if (! empty($project_ids)) {
            $projects = Project::whereIn("id", $project_ids)->get();

            foreach ($projects as $project) {
                $estimation_ids = array();
                foreach ($project->estimations as $estimation) {
                    $estimation_ids[] = $estimation->id;
                }

                ProjectProgress::whereIn('estimation_id', $estimation_ids)->delete();
                $ProjectFiles = ProjectFile::whereIn("project_id", $project_ids)->get();
                foreach ($ProjectFiles as $project_file) {
                    delete_file($project_file->file_path);
                    $project_file->delete();
                }
            }

            ProjectEstimation::whereIn('project_id', $project_ids)->delete();
            ProjectFile::whereIn("project_id", $project_ids)->delete();
            ClientProject::whereIn("project_id", $project_ids)->delete();
            UserProject::whereIn("project_id", $project_ids)->delete();
            Project::whereIn("id", $project_ids)->delete();
        }
    }
}