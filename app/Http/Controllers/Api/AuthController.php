<?php

namespace App\Http\Controllers\Api;

use Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\AuthRequest;
// use Jenssegers\Agent\Agent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function registerR(Request $request)
{
	
$validatedData = $request->validate([
'name' => 'required|string|max:255',
                   'email' => 'required|string|email|max:255|unique:users',
                   'password' => 'required|string|min:8',
]);

      $user = User::create([
              'name' => $validatedData['name'],
                   'email' => $validatedData['email'],
                   'password' => Hash::make($validatedData['password']),
       ]);

$token = $user->createToken('auth_token')->plainTextToken;

return response()->json([
              'access_token' => $token,
                   'token_type' => 'Bearer',
]);
}

public function login(Request $request)
{
	// return 'hi';
if (!Auth::attempt($request->only('email', 'password'))) {
return response()->json([
'message' => 'Invalid login details'
           ], 401);
       }

$user = User::where('email', $request['email'])->firstOrFail();
// if (!$user) {
// 	throw new Exception("Invalid email or password!");
// }
$token = $user->createToken('auth_token')->plainTextToken;

return response()->json([
			'message' => 'Login successful.',
           'access_token' => $token,
           'token_type' => 'Bearer',
]);
}

public function getUser()
	{
		// $userID = AppLogin::where("session_id", "=", $sessionID)->pluck('user_id')->first();
		return User::All();
	}

public function loginnn(Request $request){    
    $v = Validator::make ( $request->input (), 
                   [
                   "staff_id" => "required|string|min:4",
                   "password" => "required|string|min:8",
                   "role" => "required|int",
                   "device_token" => "required|string",
                   ] );
   if ($v->fails ()) {
       $error_description = "";
       foreach ( $v->messages ()
           ->all () as $error_message ) {
           $error_description .= $error_message . " ";
       }
        return  apiResponse(400, $error_description);
   } 

   $staff_id = strtoupper($request->staff_id);
   $user = User::where('staff_id', $staff_id)->where('role_id', $request->role)->where('status', User::USER_ACTIVE)->first();

   if(empty($user)){
   
      return apiResponse(400, "User data not found!");
   }

   if (Hash::check($request->password, $user->password)) {
      
       $api_token = Str::random(80);


       $user->updateDeviceToken($request->device_token);
   
       $user->updateApiToken($api_token);

       $user->refresh();

       $user_data = $user->getProfile();

       return apiResponse(200, "Login successful", $user_data);

   }else{
      return apiResponse(400, "Password mismatch!");
   }

}

private $statusOK = 200;
	private $statusCreated = 201;
	private $statusError = 403;
	private $statusErrorOk = 200;
	private $statusNotFound = 404;
	protected $type_id=array(1,2,3,4,5,6,7);
	const UPLOAD_IMAGE_PATH = "/public/profile_images/";

	/**
     * Return number of days during which bag should be returned
     */
    private function getReturnPeriod()
    {
        return AdditionalInfo::where('id', 1)->first()->days;
    }
    
	/**
	 * Return bag price
	 */
	private function getBagPrice($planId)
	{
		return AdditionalInfo::where('id', 1)->first()->price;
	}

	/**
	 * Return bag price
	 */
	private function getProductPrice()
	{
		return AdditionalInfo::where('id', 1)->first()->price;
	}

	/**
	 * Return user by session id
	 */
	private function getUserr($sessionID)
	{
		$userID = AppLogin::where("session_id", "=", $sessionID)->pluck('user_id')->first();
		return User::find($userID);
	}

	private function sendResetEmail($email,$token)
	{
		$user = User::where('email', $email)->select('name', 'email')->first();
		$link=url('/api/reset-password/'.$token);
		$sendMail = Mail::send('emails.reset-pass', ['user' => $user,'link' => $link], function ($m) use ($user) {
			$m->from('info@Bagito.co', 'Bagito');
			$m->to($user->email, $user->name)->subject('Reset Password');
		});
		return 1;
	}

    public function loginN(Request $request){
		$email = $request->email;
		$password = $request->password;
		$v = Validator::make( $request->input (), 
		    [  
				"email" => "required|email",
				"password" => "required",
				"device_type" => "required|in:android,ios,web",												  
			]);
		if($v->fails ()){
			$error_description = "";
			foreach( $v->messages()->all () as $error_message ){
					        $error_description .= $error_message . " ";
			}
			$statusCode = 400;
			$response = [       
				"status" => $statusCode,
				"message" => $error_description
			];
			return response ()->json ( $response, 200, $headers = [ ],$options = JSON_PRETTY_PRINT );
		} 			
		$user = User::where('email', $email)->first();
		if($user && Hash::check($password, $user->password)){
			$device_type='';
				if($request->device_type == "android"){
					$device_type = 1;
				}elseif($request->device_type == "ios"){
					$device_type = 2;
				}else{
					$device_type = 0;
				}
					  
		    $user_check_state = User::where('email', $email)->where('role',0)->first();
		        if($user_check_state){
					$notificationStatus = "";
					$notificationMessage = "";
						if($user_check_state->is_notification == 1){
							$notificationMessage = "Notifications are enabled.";
							$notificationStatus = 1;
						}else{
							$notificationMessage = "Notifications are disabled.";
							$notificationStatus = 0;
						}
		           $app_login = AppLogin::updateOrCreate(
		           		['user_id' => $user->id],
		                [
		                    "user_id" => $user->id,
		                    "session_id" => str::random ( 32 ),
		                    "device_type" =>  $device_type,
							"device_token"=>$request->device_token							   
		                ]);
					$statusOK = 200;
					$response = [
					    "status" => $statusOK, 
						"notification_status" => $notificationStatus,
						"notification_message" => $notificationMessage,
						"message" => "LoggedIn", 
						"profile" => $this->user_profile_response($user, $app_login ["session_id"]) 
					];
					return response()->json($response, 200, $headers = [], $options = JSON_PRETTY_PRINT);
				}else{
					$statusOK = 400;
					$response = ["status" => $statusOK, "success" =>false, "message" => "Your profile is not verified for any further details please contact us."];
					return response()->json($response, 200, $headers = [], $options = JSON_PRETTY_PRINT);
				}
		}else{
			$statusOK = 400;
			$response = ["status" => $statusOK, "success" =>false, "message" => "email or password not matched"];
			return response()->json($response, 200, $headers = [], $options = JSON_PRETTY_PRINT);
		}
	}

	public function register(AuthRequest $request){	
		
		
		// $user_chek = User::where('email', $request->email)->first(); 
	 	// if($user_chek){
	 	// 	$status =400;
	 	// 	$response = ["status" => $status, "success" =>false, "message" => "This Email is already registered"];
	 	// 	return response()->json($response, 200, $headers = [], $options = JSON_PRETTY_PRINT);
	 	// }
		
		// $name = $request->username." ".$request->last_name;

		$user = new User();
	 	// $user->name = $name;
		$user->first_name = $request->first_name;
		$user->last_name = $request->last_name;
	 	$user->email = $request->email;
	 	// $user->address=$request->address;
		// $user->city = $request->city;
		// $user->state = $request->state;
		// $user->zipcode = $request->zipcode;
	 	$user->password = Hash::make($request->password);
	 	// $user->remember_token = $request->device_token;
		$user->phone = $request->phone;
	 	// $user->role = 0;
	 	$user->save();
	 	// if($user){
	    //     $app_login = AppLogin::updateOrCreate(
	    //         [ "user_id" => $user->id],
	    //         [
	    //             "user_id" => $user->id,
	    //             "session_id" => Str::random(32),
	    //             "device_type" => $request->device_type == "android" ? AppLogin::DEVICE_ANDROID : ($request->device_type ==
	    //                              "ios" ? AppLogin::DEVICE_IPHONE : AppLogin::DEVICE_TEST),
	    //             'device_token'=>$request->device_token
		// 		] );
	 
	    //     $app = AppLogin::where('user_id',$user->id)->first();
	 	// 	$status = 200;
	 	// 	$response = ["status" => $status, "success" =>true, "message" => "user registered successfully","profile" => $this->user_profile_response($user,$app->session_id) ];
	 	// 		    	return response()->json($response, 200, $headers = [], $options = JSON_PRETTY_PRINT);
	 	// }
	 	// $response=["status"=>200, "success"=>true, "message"=>"user registered successfully"];
	 	// return response()->json($response,200,$header=[],$options=JSON_PRETTY_PRINT); 
	}

	public function logout(Request $request){
	    $statusCode = 200;
        $response = [];
        $session_id = $request->session_id;
        $session = AppLogin::where("session_id", "=", $session_id)->first();

        if(!empty($session)){
            AppLogin::where('user_id',$session->user_id)->where("session_id", "=", $session_id)->delete();
            $response =  ["status" => $statusCode, "message" => "Logged out", ];
            return response()->json($response, 200, $headers = [], $options = JSON_PRETTY_PRINT);
        }
        else{
            $response = ["status" => 403, "message" => "Session already expired"];
            return response()->json($response, 200, $headers = [], $options = JSON_PRETTY_PRINT);
        }
	}

	public function notificationsOnOff(request $request){
        $statusCode = 200;
        $response = [];
        $session_id = $request->session_id;
        $session = AppLogin::where("session_id", "=", $session_id)->first();

        if(!empty($session)){
            $v = Validator::make ( $request->input (),
	                [
	 					"notification" =>"required|in:true,false" ,			   
	                ] );
	 		if($v->fails ()){
	 			$error_description = "";
	 			foreach ( $v->messages()->all () as $error_message ){
	 			    $error_description .= $error_message . " ";
	 			}
				$statusCode = 400;
				$response = [	       
					"status" => $statusCode,
					"message" => $error_description
				];
	 			return response ()->json ( $response, 200, $headers = [ ],$options = JSON_PRETTY_PRINT );
	 		} 
			$user_id=$this->user_id($session_id);
			$is_notification = "";
			if($request->notification=="true"){
                $is_notification=1;
				$statusCode=200;
				$message="Notification is on";
				$notificationStatus = 1;
			}else{
                $is_notification=0;
				$message="Notification is off";
				$notificationStatus = 0;
			}
			$result=$this->updateNotification($user_id,$is_notification);
			if($result){
				$response = ["status" => 200, "success" =>true, "message" => $message, 'notification_status' => $notificationStatus];
			}else{
				$message="getting some issue";
				$statusCode=400;
                $response = ["status" => $statusCode, "success" =>false, "message" => $message, 'status' => $status];
			}
            return response()->json($response, 200, $headers = [], $options = JSON_PRETTY_PRINT);
        }
        else{
            $response = ["status" => 403, "success" =>false, "message" => "Session already expired"];
            return response()->json($response, 200, $headers = [], $options = JSON_PRETTY_PRINT);
        }
}
}