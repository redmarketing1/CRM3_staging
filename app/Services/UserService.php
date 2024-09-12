<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailTemplate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserService
{
    public function genericCreateFunc($modelName, $predefinedModelname = null, $predefinedModelId = null, $data = null, $ajax_request = false) {
    
        $return         = array('status' => true);
        $is_new         = false;
        $need_delete    = false;
        if ($predefinedModelname == 'new') {
            $is_new = true;
        } else if ($predefinedModelId == 0){
            $is_new = true;
        } else {
			$is_new     = true;
			$need_delete= true;    
        }

        $user_id = 0;
        if ($is_new == true) {
            if ($need_delete == true) {
                $user_id = $predefinedModelId;
            } else {
                $user = new User();
            }
        }

        if ($user_id > 0) {
            $user   = User::find($user_id);
        }

        if ($user_id == 0) {
            $validation_array = array('last_name' => 'required|max:120');

            if (isset($data['send_access']) && ($data['send_access'] == 'on') || $data['email'] != '') {
                $validation_array['email'] = [
                    'required',
                    'email',
                    Rule::unique('users')->where(function ($query) {
                        return $query->where('created_by', Auth::user()->id);
                    })
                ];
            }

            $validator = Validator::make(
                $data,
                $validation_array
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                $return['status'] = false;
                $return['message']= $messages->first();
            }
        }

        if ($return['status'] == true) {
            $user_type = $modelName;

            $user->type = $user_type;
            $user->name = $data['first_name'].' '.$data['last_name'];
            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];
            $user->email = $data['email'];
            $user->company_name = $data['company_name'];
            
            $user->title = isset($data['title']) ? $data['title'] : null;
            $user->salutation = isset($data['salutation']) ? $data['salutation'] : null;
            $user->mobile_no = $data['mobile_no'];
            $user->phone = $data['phone'];
            $user->address_1 = $data['address_1'];
            $user->address_2 = !empty($data['address_2']) ? $data['address_2'] : '';
            $user->tax_number = !empty($data['tax_number']) ? $data['tax_number'] : '';
            $user->website = !empty($data['website']) ? $data['website'] : '';
            $user->city = $data['city'];
            $user->district_1 = $data['district_1'];
            $user->district_2 = $data['district_2'];
            $user->state = $data['state'];
            $user->country = isset($data['country']) ? $data['country'] : 0;
            $user->zip_code = $data['zip_code'];
            $user->notes = !empty($data['notes']) ? $data['notes'] : '';
            
            $user->lat = $data['latitude'];
            $user->long = $data['longitude'];
            $user->is_enable_login = (isset($data['is_enable_login']) && ($data['is_enable_login'] == 'on')) ? 1 : 0;
            if (isset($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            if (!isset($user->id)) {
                $user->lang = 'en';
                $user->created_by = Auth::user()->id;
				$user->workspace_id = getActiveWorkSpace();
                $user->email_verified_at = date("H:i:s");
            }
            // $user->avatar = '';
    
            if (isset($data['profile'])) {
				$filenameWithExt = $data['profile']->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $data['profile']->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
				 
                $request = new \Illuminate\Http\Request();
                $request->profile = $data['profile'];

				$path = upload_file($request, 'profile', $fileNameToStore, 'users-avatar');
    
				// Old img delete
                if (!empty($user->avatar) && strpos($user->avatar, 'avatar.png') == false && check_file($user->avatar)) {
                    delete_file('uploads/users-avatar/' .$user->avatar);
                }
    
                if ($path['flag'] == 1) {
                    $user->avatar = 'uploads/users-avatar/' .$fileNameToStore;
                } else {
                    // return redirect()->back()->with('error', __($path['msg']));
                }
            }
    
            $user->save();
    
            if ($is_new == true && isset($data['send_access']) && ($data['send_access'] == 'on')) {
                $this->send_access_mail($user->id, $data['password']);
            }
    
            $return['user'] = $user;
        }

        return $return;
    }

    public function send_access_mail($user_id = 0, $password = "") {
        $return = array();

        if ($user_id > 0) {
            $user = User::find($user_id);
            if ($user) {
                if ($password == "") {
                    $password = random_int(1000, 9999);
                    $user->password = Hash::make($password);
                    $user->save();
                }
                
                $uArr = [
                    'email' => $user->email,
                    'password' => $password,
                ];

                $resp = EmailTemplate::sendEmailTemplate('New User', [$user->email], $uArr);
                if($resp['is_success'] == true){
                    $return['status'] = true;
                    $return['message']= __('Mail sent to contact.');
                } else {
                    $return['status'] = false;
                    $return['message']= __('Something went wrong.');
                }
            } else {
                $return['status'] = false;
                $return['message']= __('Something went wrong.');
            }
        } else {
            $return['status'] = false;
            $return['message']= __('Something went wrong.');
        }

        return $return;
    }
}
