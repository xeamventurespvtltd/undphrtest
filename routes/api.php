<?php



use Illuminate\Http\Request;



/*

|--------------------------------------------------------------------------

| API Routes

|--------------------------------------------------------------------------

|

| Here is where you can register API routes for your application. These

| routes are loaded by the RouteServiceProvider within a group which

| is assigned the "api" middleware group. Enjoy building your API!

|

*/



Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();

});



Route::group(['prefix'=>'v1'], function(){
	Route::post('login','API\UserController@login');
	Route::post('app-version','API\UserController@checkAppVersion');
	
	Route::group(['middleware' => 'auth:api'], function(){
		Route::get('logout','API\UserController@logout');
		Route::post('departments-wise-employees','API\UserController@departmentsWiseEmployees');
		
		Route::post('attendance-detail','API\AttendanceController@attendanceDetail');
		Route::post('attendance-punches','API\AttendanceController@attendancePunches');

		Route::post('user-attendance-detail','API\AttendanceController@userAttendanceDetail');
		Route::post('monthly-attendance-report','API\AttendanceController@monthlyAttendanceReport');

		Route::post('attendance-location','API\AttendanceController@storeAttendanceLocation');
		Route::get('departments','API\MasterController@departments');

		Route::get('leave-balance','API\LeaveController@leaveBalance');
		Route::post('applied-leaves','API\LeaveController@appliedLeaves');

	});
});

