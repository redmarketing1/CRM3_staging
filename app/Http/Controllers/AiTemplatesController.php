<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\ContentTemplateLang;
use App\Models\Content;

class AiTemplatesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index($id = null, $lang = 'en')
	{
		//if (Auth::user()->isAbleTo('ai template manage')) {
		if ($id != null) {
			$notification_template     = Content::where('id', $id)->first();
		} else {
			//	$notification_template     = Content::whereIn("id", array(27, 28, 29, 30, 31, 32))->first();
			$notification_template     = Content::where('is_ai', 1)->first();
		}

		if (empty($notification_template)) {
			return redirect()->back()->with('error', __('Not exists in AI template.'));
		}
		$languages         	= languages();
		$curr_noti_tempLang = ContentTemplateLang::where('parent_id', '=', $notification_template->id)->where('lang', $lang)->where('created_by', '=', Auth::user()->id)->first();
		if (!isset($curr_noti_tempLang) || empty($curr_noti_tempLang)) {
			$curr_noti_tempLang       = ContentTemplateLang::where('parent_id', '=', $notification_template->id)->where('lang', $lang)->first();
		}
		if (!isset($curr_noti_tempLang) || empty($curr_noti_tempLang)) {
			$curr_noti_tempLang       = ContentTemplateLang::where('parent_id', '=', $notification_template->id)->where('lang', 'en')->first();
			!empty($curr_noti_tempLang) ? $curr_noti_tempLang->lang = $lang : null;
		}
		$notification_templates = Content::where('is_ai', 1)->get();

		return view('ai_templates.index', compact('notification_template', 'notification_templates', 'curr_noti_tempLang', 'languages'));
		// } else {
		// 	return redirect()->back()->with('error', __('Permission denied.'));
		// }
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(string $id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(string $id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, string $id)
	{
		$validator = Validator::make(
			$request->all(),
			[
				'content' => 'required',
			]
		);
		if ($validator->fails()) {
			$messages = $validator->getMessageBag();
			return redirect()->back()->with('error', $messages->first());
		}
		$ContentTemplateLang = ContentTemplateLang::where('parent_id', '=', $id)->where('lang', '=', $request->lang)->where('created_by', '=', Auth::user()->id)->first();
		//if record not found then create new record else update it.
		if (empty($ContentTemplateLang)) {
			$variables = ContentTemplateLang::where('parent_id', '=', $id)->where('lang', '=', $request->lang)->first();
			$ContentTemplateLang            	= new ContentTemplateLang();
			$ContentTemplateLang->parent_id 	= $id;
			$ContentTemplateLang->lang      	= $request['lang'];
			$ContentTemplateLang->content   	= $request['content'];
			$ContentTemplateLang->variables 	= isset($variables->variables) ? $variables->variables : '';
			$ContentTemplateLang->created_by 	= Auth::user()->id;
			$ContentTemplateLang->workspace 	= getActiveWorkSpace();
			$ContentTemplateLang->save();
		} else {
			$ContentTemplateLang->content 		= $request['content'];
			$ContentTemplateLang->save();
		}
		return redirect()->back()->with('success', __('AI Template successfully updated.'));
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(string $id)
	{
		//
	}
}
