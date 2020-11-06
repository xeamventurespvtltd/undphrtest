@extends('admins.layouts.app')

@section('content')

<style>
.select2-container .select2-selection--single
{
  height: 30px;
  border: 1px solid #d2d6de;
  box-shadow: 0px 1px 2px lightgrey;
  font-size: 12px;
  padding: 5px 0px;
}
</style>

<!-- Content Wrapper. Contains page content -->
<!-- Bootstrap time Picker -->
<link rel="stylesheet" href="{{asset('public/admin_assets/plugins/timepicker/bootstrap-timepicker.min.css')}}">

  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1 class="text-center">
        Travel Approval Form
      </h1>

      <ol class="breadcrumb breadcrumb-leave-change">
        <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
          <div class="col-sm-12">
           <div class="box box-primary">
           
                @include('admins.validation_errors')
                
            <div class="box-header with-border leave-form-title-bg">
              <h3 class="box-title">Approval Form</h3>
              <span class="pull-right"></span>
            </div>            
            <form id="travelApprovalForm" action="" method="POST" enctype="multipart/form-data">
              {{ csrf_field() }}
              <div class="box-body form-sidechange form-decor">                                                    
                <!-- Time input section stasts here -->
                <legend>Travel Request Form</legend>

                  <!-- Travel type section stasts here -->
                <div class="travelTypeBox">
                  <div class="btn-group apply-leave-btn-all">
                    <button type="button" class="btn btn-primary select_local select_travel_type" id="local_travel">Local</button>
                    <button type="button" class="btn btn-primary select_national select_travel_type">National</button>
                  </div>
                </div>

                <div class="form-group">
                  <div class="row">
                    <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box">
                        <label class="apply-leave-label">Travel for<span style="color: red">*</span></label>    
                    </div>
                    <div class="col-md-6 leave-input-box travel-input-box-client">
                       <label class="radio-inline">
                         <input required="" class="customer_type_selection" type="radio" name="isclient" value="0"> Existing Customer
                       </label>
                       <label class="radio-inline">
                         <input required="" class="customer_type_selection" type="radio" name="isclient" value="1"> Future Customer
                       </label>
                       <label class="radio-inline">
                         <input required="" class="customer_type_selection" type="radio" name="isclient" value="2"> Others
                       </label>
                    </div>
                    <div class="col-md-4 leave-input-box travel_customerss">
                      <div class="for_existing_customer">
                        <select name="" id="" class="form-control select2 input-sm basic-detail-input-style" data-placeholder="Existing customer">
                          <option value=""></option>
                          <option value=""></option>
                          <option value=""></option>
                        </select>
                      </div>

                      <div class="for_future_customer">
                        <select name="" id="" class="form-control select2 input-sm basic-detail-input-style"  data-placeholder="Future customer">
                          <option value=""></option>
                          <option value=""></option>
                          <option value=""></option>
                        </select>
                      </div>
                      
                      <div class="other_customers">
                        <input type="text" name="" id="" class="form-control input-sm basic-detail-input-style" placeholder="Enter your other Purpose">
                        <!-- <textarea class="form-control other_purpose_description" name="" id="" placeholder="Enter your other Purpose"></textarea> -->
                      </div>
                    </div>
                  </div>
                </div>

                <!-- local section starts here -->
                <div class="local_travel_section">
                  <div class="form-group">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="row">
                          <div class="col-md-4 travel-label-box">
                            <label for="approval_duration">Approval Duration<span style="color: red">*</span></label>
                          </div>
                          <div class="col-md-8">
                            <select required="" class="form-control select2 state city_select input-sm basic-detail-input-style" name="approval_duration" id="approval_duration">
                              <option value="">Select Duration</option>
                              <option value="Monthly">Monthly</option>
                              <option value="One Time">One Time</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="row">
                          <div class="col-md-4 travel-label-box">
                            <label for="">City<span style="color: red">*</span></label>
                          </div>
                          <div class="col-md-8">
                            <select required="" class="form-control select2 state city_select input-sm basic-detail-input-style" name="city_id_to_pre" id="cityId2">
                            <option value="">Select City</option>
                            <option value="1">Test city</option>
                          </select>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="row">
                          <div class="col-md-4 travel-label-box">
                            <label for="local_conveyance">Conveyance<span style="color: red">*</span></label>
                          </div>
                          <div class="col-md-8">
                            <select required="" class="form-control select2 state city_select input-sm basic-detail-input-style" name="local_conveyance" id="local_conveyance">
                              <option value="">Select Convenyance</option>
                              <option value="Flight">Flight</option>
                              <option value="Train">Train</option>
                              <option value="Bus">Bus</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="row">
                          <div class="col-md-4 travel-label-box">
                            <label for="">Amount<span style="color: red">*</span></label>
                          </div>
                          <div class="col-md-8">
                            <input required autocomplete="" type="number" class="form-control input-sm basic-detail-input-style amount_to_be include_cal" id=""
                              name="local_travel_amount" min="0" value="" placeholder="Amount">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              <!-- national section starts here -->
              <div class="national_travel_section" style="display: none;">


                <table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Date<span style="color: red">*</span></th>
                      <th>City (from)<span style="color: red">*</span></th>
                      <th>City (to)<span style="color: red">*</span></th>
                      <th>Conveyance<span style="color: red">*</span></th>
                      <th>Amount<span style="color: red">*</span></th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="tbodyNational">
                    <tr class="city_tr">
                      <td>
                        <input required="" autocomplete="off" type="text" class="form-control selectDate datepicker input-sm basic-detail-input-style" id="travel_date_range" name="travel_date_range" placeholder="MM/DD/YYYY" value="" readonly>
                      </td>
                      <td>
                        <select required="" class="form-control select2 state city_select input-sm basic-detail-input-style" name="city_id_from_pre" id="cityId11">
                          <option value="">Select City</option>
                        </select>
                      </td>
                      <td>
                        <select required="" class="form-control select2 state city_select input-sm basic-detail-input-style" name="city_id_to_post" id="cityId12">
                          <option value="">Select City</option>
                        </select>
                      </td>
                      <td>
                        <select required="" class="form-control select2 input-sm basic-detail-input-style" name="conveyance_id[]" id="conveyance_id" placeholder="Please select conveyance" >
                          <option value="">Select Conveyance</option>
                                @if($data['user']->designation[0]->band->travel_conveyances->count())
                                  @foreach($data['user']->designation[0]->band->travel_conveyances as $conveyance)
                                    <option value="{{$conveyance->id}}">{{$conveyance->name}}</option>
                                  @endforeach
                                @endif
                        </select>
                      </td> 
                      <td>
                        <input required autocomplete="" type="number" class="form-control input-sm basic-detail-input-style amount_to_be include_cal" id=""
                            name="expected_amount" min="0" value="" placeholder="Amount">
                      </td>
                      <td></td>
                    </tr>
                  </tbody>
                </table>                  
                <div class="form-group">
                  <div class="row text-center">
                    <a href="javascript:void(0);" class="btn btn-success" onclick="addMoreNatoinal()">Add More</a>
                  </div>
                </div>

                 
            </div>
            <!-- national section ends here -->


                                                  <!-- Date input section ends here -->
                
              
                <div class="form-group">
                  <div class="row">
                      <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box">
                          <label class="apply-leave-label">Project & Purpose<span style="color: red">*</span></label>
                      </div>
                      <div class="col-md-3 col-sm-2 col-xs-2 leave-input-box">
                          <select required="" class="form-control" name="project_id_pre" id="project_id_pre" placeholder="Please select project">
                            <option value="">Please select project</option>
                            @if($data['projects']->count())
                            @foreach($data['projects'] as $project)
                            <option value="{{$project->id}}">{{$project->name}}</option>
                            @endforeach
                            @endif
                          </select>
                      </div>
                      <div class="col-md-7">
                        <input autocomplete="off" type="text" class="form-control" id=""
                        name="purpose_pre" value=""  required="" placeholder="Enter your purpose for travel">
                      </div>
                  </div>
                </div>
                <div class="form-group hide">
                  <div class="row">
                      <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box">
                        <label class="apply-leave-label">Opportunities<span style="color: red">*</span></label>
                      </div>
                      <div class="col-md-3 col-sm-2 col-xs-2 leave-input-box">
                         <select required="" class="form-control select2" name="opportunities[]" multiple="multiple" style="width:100%;">
                            <option>Please select project</option>
                            @if($data['projects']->count())
                            @foreach($data['projects'] as $project)
                            <option value="{{$project->id}}">{{$project->name}}</option>
                            @endforeach
                            @endif
                          </select>
                      </div>
                      <div class="col-md-7">
                        <input autocomplete="off" type="text" class="form-control" id=""
                        name="purpose_opportunity" value=""  required="" placeholder="Enter your purpose for travel">
                      </div>
                  </div>
                </div>


            <div class="onlyAlowedForNational">
                <div class="form-group">
                  <div class="row">
                      <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box">
                        <label class="apply-leave-label">Covered under policy<span style="color: red">*</span></label>
                      </div>
                      <div class="col-md-3 col-sm-2 col-xs-2 leave-input-box">
                           <label class="radio-inline">
                             <input type="radio" required="" name="under_policy" value="1"> Yes
                           </label>
                           <label class="radio-inline">
                             <input type="radio" required="" name="under_policy" value="0"> No
                           </label>
                      </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="row">
                      <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box">
                          <label class="apply-leave-label">Stay<span style="color: red">*</span></label>
                      </div>
                      <div class="col-md-3 col-sm-2 col-xs-2 leave-input-box">
                           <label class="radio-inline">
                             <input type="radio" required="" name="stay" onclick="CheckStay(1);" value="1"> Yes
                           </label>
                           <label class="radio-inline">
                             <input type="radio" required="" name="stay" onclick="CheckStay(0);" value="0"> No
                           </label>
                      </div>
                  </div>
                </div>
                <div class="row1 hide" id="stay_block">
                    <legend>Stay Form</legend>
                    <div class="col-md-12">
                      <table class="table table-bordered table-striped">
                        <thead>
                          <tr>
                            <th>Dates</th>
                            <th>State</th>
                            <th>City</th>
                            <th>Rate stay/night</th>
                            <th>DA</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody id="tbody">
                          <tr class="city_tr">
                            <td>
                              <input style="width: 200px;" autocomplete="off" type="text" class="form-control selectDate stay_date_range" id="" name="stay_date_range[]" placeholder="MM/DD/YYYY" value="" readonly>
                            </td>
                            <td>
                              <select required="" class="form-control  state" name="state_id_stay" onchange="displayCity($(this), 3);"  style="width: 120px">
                                  <option value="">Select state</option>
                                  @if(!$data['states']->isEmpty())
                                    @foreach($data['states'] as $state)  
                                    <option value="{{$state->id}}">{{$state->name}}</option>
                                    @endforeach
                                  @endif
                              </select>
                            </td>
                            <td>
                              <select onchange="GetCityDetails($(this))" class="form-control state city_select" name="city_id_stay[]" required="" id="" style="width: 120px">
                                <option value="">Select City</option>
                              </select>
                            </td>
                            <td><input required type="number" class="form-control stayda amount_to_be rate_per_night" id=""
                          name="rate_per_night[]" min="0" value="" placeholder="Rate per night"></td> 
                            <td><input required autocomplete="" type="number" class="form-control amount_to_be stayda da_class" id=""
                          name="da[]" min="0" value="0" placeholder="DA"></td>
                            <td></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  <div class="form-group">
                    <div class="row text-center">
                      <a href="javascript:void(0);" class="btn btn-success" onclick="addMoreRows()">Add More</a>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="row">
                      <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box">
                          <label class="apply-leave-label">Other financial approvals<span style="color: red">*</span></label>
                      </div>
                      <div class="col-md-3 col-sm-2 col-xs-2 leave-input-box">
                           <label class="radio-inline">
                             <input required="" type="radio" name="other_financial_approval" onclick="CheckOther(1);" value="1"> Yes
                           </label>
                           <label class="radio-inline">
                             <input required="" type="radio" name="other_financial_approval" onclick="CheckOther(0);" value="0"> No
                           </label>
                      </div>
                  </div>
                </div>
                <div class="row1 hide" id="other_financial_block">
                  <legend>Other Financial Approvals Form</legend>
                  <div class="form-group">
                    <div class="row">
                      <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box">
                          <label class="apply-leave-label">Location<span style="color: red">*</span></label>
                      </div>
                      <div class="col-md-3 col-sm-2 col-xs-2 leave-input-box">
                          <select required="" class="form-control" name="" id="">
                            @if(!$data['countries']->isEmpty())
                              @foreach($data['countries'] as $country)  
                                <option value="{{$country->id}}" @if($country->name == "India"){{"selected"}}@endif>{{$country->name}}</option>
                              @endforeach
                            @endif  
                          </select>
                      </div>
                      <div class="col-md-4 col-sm-2 col-xs-2 leave-input-box">
                          <select required="" class="form-control  state" name="" id="" onchange="displayCity($(this), 4);">
                                <option value="">Please select state</option>
                            @if(!$data['states']->isEmpty())
                              @foreach($data['states'] as $state)  
                                <option value="{{$state->id}}">{{$state->name}}</option>
                              @endforeach
                            @endif
                          </select>
                      </div>
                      <div class="col-md-3 col-sm-2 col-xs-2 leave-input-box">
                          <select required=""  class="form-control state city_select " name="city_id_other" id="cityId4">
                            <option value="">Select City</option>
                          </select>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="row">
                      <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box">
                          <label class="apply-leave-label">Project & Purpose<span style="color: red">*</span></label>
                      </div>
                      <div class="col-md-3 col-sm-2 col-xs-2 leave-input-box">
                          <select required=""  class="form-control" name="project_id_other" id="project_id_other" placeholder="Please select project">
                            <option value="">Please select project</option>
                            @if($data['projects']->count())
                            @foreach($data['projects'] as $project)
                            <option value="{{$project->id}}">{{$project->name}}</option>
                            @endforeach
                            @endif
                          </select>
                      </div>
                      <div class="col-md-7">
                        <input autocomplete="off" type="text" class="form-control" id=""
                        name="purpose_other" value=""   placeholder="Enter your purpose">
                        </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="row">
                      <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box">
                          <label class="apply-leave-label">Amount<span style="color: red">*</span></label>
                      </div>
                      <div class="col-md-3 col-sm-2 col-xs-2 leave-input-box">
                        <input type="number" class="form-control amount_to_be include_cal" id=""
                        name="amount_other" min="0" value="" placeholder="Amount">
                      </div>
                      
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="row">
                      <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box">
                          <label class="apply-leave-label">Imprest Request<span style="color: red">*</span></label>
                      </div>
                      <div class="col-md-3 col-sm-2 col-xs-2 leave-input-box">
                           <label class="radio-inline">
                             <input required="" type="radio" name="imprest_request" onclick="CheckImprest(1);" value="1"> Yes
                           </label>
                           <label class="radio-inline">
                             <input required="" type="radio" name="imprest_request" onclick="CheckImprest(0);" value="0"> No
                           </label>
                      </div>
                  </div>
                </div>
                <div class="row1 hide" id="imprest_block">
                  <legend>Imprest Request Form</legend>
                  <div class="form-group">
                    <div class="row">
                      <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box">
                          <label class="apply-leave-label">Project & Remarks<span style="color: red">*</span></label>
                      </div>
                      <div class="col-md-3 col-sm-2 col-xs-2 leave-input-box">
                          <select required="" class="form-control" name="project_id_imprest" id="project_id_imprest" placeholder="Please select project">
                            <option value="">Please select project</option>
                            @if($data['projects']->count())
                            @foreach($data['projects'] as $project)
                            <option value="{{$project->id}}">{{$project->name}}</option>
                            @endforeach
                            @endif
                          </select>
                      </div>
                      <div class="col-md-7">
                        <input autocomplete="off" type="text" class="form-control" id=""
                        name="remarks" value="" required="" placeholder="Enter your remarks">
                        </div>
                    </div>
                  </div>
                  <div class="form-group hide">
                    <div class="row">
                      <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box">
                          <label class="apply-leave-label">Total Amount</label>
                      </div>
                      <div class="col-md-3 col-sm-2 col-xs-2 leave-input-box">
                        <input type="text" class="form-control" id="total_amount" disabled="" 
                        name=""  value="" placeholder="">
                      </div>
                      
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="row">
                      <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box">
                          <label class="apply-leave-label">Amount for imprest<span style="color: red">*</span></label>
                      </div>
                      <div class="col-md-3 col-sm-2 col-xs-2 leave-input-box">
                        <input type="number" class="form-control" id="amount_imprest"
                        name="amount_imprest" min="0" value="" placeholder="Amount">
                      </div>
                      
                    </div>
                  </div>
                </div>
            </div>

                <div class="form-group">
                  <div class="row">
                      <div class="col-md-12">
                          <button type="submit" class="btn btn-primary" name="btn_submit" value="submit" id="">Submit</button>
                      </div>
                  </div>
                </div>
              </div>              
            </form>
          </div>
      </div>
          <!-- /.box -->
      </div>
      <!-- /.row -->
      <!-- Main row -->
    </section>
    <!-- /.content -->
        <!-- /.modal --> 
  </div>


<!-- Appended rows for national starts here-->
<div class="hide" id="clonenational">
    <table>
    <tr id="" class="removetr city_tr">
      <td>
        <input required="" autocomplete="off" type="text" class="form-control selectDate datepicker input-sm basic-detail-input-style" id="travel_date_range" name="travel_date_range" placeholder="MM/DD/YYYY" value="" readonly>
      </td>
      <td>
        <select required="" class="form-control state city_select input-sm basic-detail-input-style" id="cityId11">
          <option value="">Select City</option>
          <option value="Shellosdf">Shellosdf</option>
          <option value="Czvzity">Czvzity</option>
        </select>
      </td>
      <td>
        <select required="" class="form-control state city_select input-sm basic-detail-input-style" name="city_id_to_post" id="cityId12">
          <option value="">Select City</option>
        </select>
      </td>
      <td>
        <select required="" class="form-control input-sm basic-detail-input-style conveyance_cloned" name="conveyance_id[]" id="conveyance_id" placeholder="Please select conveyance">
          <option value="">Select Conveyance</option>
                @if($data['user']->designation[0]->band->travel_conveyances->count())
                  @foreach($data['user']->designation[0]->band->travel_conveyances as $conveyance)
                    <option value="{{$conveyance->id}}">{{$conveyance->name}}</option>
                  @endforeach
                @endif
        </select>
      </td>
      <td>
        <input required autocomplete="" type="number" class="form-control input-sm basic-detail-input-style amount_to_be include_cal" id="" name="expected_amount" min="0" value="" placeholder="Amount">
      </td>
      <td><a href="javascript:void();" onclick="removeThisNationalTr($(this))" class="btn btn-danger btn-xs">Remove</a></td>
    </tr>
  </table>
  </div>
<!-- Appended rows for national ends here -->







  <div class="hide" id="cloneit">
    <table>
    <tr id="" class="removetr city_tr">
      <td>
        <input style="width: 200px;" autocomplete="off" type="text" class="form-control selectDate stay_date_range" id="" name="stay_date_range[]" placeholder="MM/DD/YYYY" value="" readonly>
      </td>
      <td>
        <select required="" class="form-control select2  state" name="state_id_stay" onchange="displayCity($(this), 3);"  style="width: 120px">
            <option value="">Select state</option>
          @if(!$data['states']->isEmpty())
            @foreach($data['states'] as $state)  
              <option value="{{$state->id}}">{{$state->name}}</option>
            @endforeach
          @endif
        </select>
      </td>
      <td>
        <select onchange="GetCityDetails($(this))" class="form-control state city_select" required="" name="city_id_stay[]" id="cityId3" style="width: 120px">
          <option value="">Select City</option>
        </select>
      </td>
      <td><input required type="number" class="form-control stayda amount_to_be rate_per_night" id=""
    name="rate_per_night[]" min="0" value="" placeholder="Rate per night"> </td>
      <td><input required autocomplete="" type="number" class="form-control amount_to_be stayda da_class" id=""
    name="da[]" min="0" value="0" placeholder="DA"></td>
      <td><a href="javascript:void();" onclick="removeThisTr($(this))" class="btn btn-danger btn-xs">Remove</a></td>
    </tr>
  </table>
  </div>
  <!-- /.content-wrapper -->
  <!-- bootstrap time picker -->
  <script src="{{asset('public/admin_assets/plugins/timepicker/bootstrap-timepicker.min.js')}}"></script>
  <script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
  <script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>
 
 <script>
        $("#travelApprovalForm").validate({
          rules :{
              "isclient" : {
                  required : true,
              },
              "approval_duration" : {
                  required : true,
              },
              "city_id_to_pre" : {
                  required : true,
              },
              "local_travel_amount" : {
                required : true,
              },
              "travel_date_range" : {
                required : true,
              },
              "city_id_from_pre" : {
                required : true,
              },
              "city_id_to_post" : {
                required : true,
              },
              "conveyance_id[]" : {
                required : true,
              },
              "expected_amount" : {
                required : true,
              },
              "local_conveyance" : {
                required : true,
              }

          },
          errorPlacement: function(error, element) {
            if (element.hasClass('select2')) {
              error.insertAfter(element.next('span.select2'));
            }
            else if (element.attr("name") == "isclient") {
                 //error.insertAfter(".travel-input-box-client");
                 error.appendTo(".travel-input-box-client");
              }
              else if (element.is(":radio")) {
                 error.insertAfter(element.parent().next());
              }
            else {
              error.insertAfter(element);
            }
          },

          messages :{
              "isclient" : {
                  required : 'Please select Customer Type'
              },
              "approval_duration" : {
                required : 'Please select time duration'
              },
              "city_id_to_pre" : {
                  required : 'Please select your city'
              },
              "local_travel_amount" : {
                required : 'Please enter local travel amount'
              },
              "travel_date_range" : {
                required : 'Please select Date'
              },
              "city_id_from_pre" : {
                required : 'Please select City'
              },
              "city_id_to_post" : {
                required : 'Please select city'
              },
              "conveyance_id[]" : {
                required : 'Please choose conveyance'
              },
              "expected_amount" : {
                required : 'Please enter the amount'
              },
              "local_conveyance" : {
                required : 'Please choose conveyance'
              },
            }
          });
</script>



<script>
$(document).ready(function(){

  $(".for_future_customer").hide();
  $(".other_customers").hide();
  $(".for_existing_customer").hide();

  $(".customer_type_selection").on('click',function(){

    var customerTypeSelection = $(this).val();
    
    if (customerTypeSelection == 0) {
      $(".for_future_customer").hide();
      $(".other_customers").hide();
      $(".for_existing_customer").show();
    }
    else if (customerTypeSelection == 1) {
       $(".other_customers").hide();
       $(".for_existing_customer").hide();
       $(".for_future_customer").show();
    }
    else {
       $(".other_customers").show();
       $(".for_existing_customer").hide();
       $(".for_future_customer").hide();
    }
  });
  });
</script>

<script>
 $(".onlyAlowedForNational").hide();

 $(".select_national").on('click',function(){
    $(".national_travel_section").show();
    $(".local_travel_section").hide();
    $(".onlyAlowedForNational").show();
  });

  $(".select_local").on('click',function(){
    $(".national_travel_section").hide();
    $(".local_travel_section").show();
    $(".onlyAlowedForNational").hide();

  });

  $("#local_travel").addClass("active");
  $(".select_travel_type").on('click',function(){
    $(".select_travel_type").removeClass("active");
    $(this).addClass("active");
  });

</script>


<script type="text/javascript">
function removeThisNationalTr(obj){
    obj.parents('.removetr').remove();
}

function addMoreNatoinal() {
  $("#clonenational table .removetr").clone().appendTo('#tbodyNational');
    $('#tbodyNational').last('tr').children().find('.datepicker')
      .datepicker();
    $('#tbodyNational').last('tr').children().find('.city_select')
      .select2();
      $('#tbodyNational').last('tr').children().find('.conveyance_cloned')
      .select2();
}
</script>



  <script type="text/javascript">
  function removeThisTr(obj){
    obj.parents('.removetr').remove();
  }
  function addMoreRows(){
    $("#cloneit table .removetr").clone().appendTo('#tbody');
    $('#tbody').last('tr').children().find('.selectDate')
      .daterangepicker();
  }
  
  function CheckStay(s){
    //Show Hide Stay block
    if(s==1){
      $('#stay_block').removeClass('hide');
      $('#stay_block').children().find('input, select').prop('required', true);
    }
    else{
      $('#stay_block').addClass('hide');
      $('#stay_block').children().find('input, select').prop('required', false);
    }
  }
  function CheckImprest(s){
    //Show Hide Imprest block
    if(s==1){
      $('#imprest_block').removeClass('hide');
      $('#imprest_block').children().find('input, select').prop('required', true);
    }
    else{
      $('#imprest_block').addClass('hide');
      $('#imprest_block').children().find('input, select').prop('required', false);
    }
  }
  function CheckOther(s){
    //Show Hide Other Financial Approval  block
    if(s==1){
      $('#other_financial_block').removeClass('hide');
      $('#other_financial_block').children().find('input, select').prop('required', true);
    }
    else{
      $('#other_financial_block').addClass('hide');
      $('#other_financial_block').children().find('input, select').prop('required', false);
    }
  }
  function CalculateDifference(){
    //calculate difference between two dates and add one day as stay per night is to be calculated
    var date1 = new Date($("#date_from_stay").val()); 
    var date2 = new Date($("#date_to_stay").val()); 
    var Difference_In_Time = date2.getTime() - date1.getTime(); 
    Difference_In_Days = (Difference_In_Time / (1000 * 3600 * 24))+1;
    $("#stay_days").val(Difference_In_Days + " Days");
    $("#no_of_days_to_stay").val(Difference_In_Days);
    calculateTotalAmountToRequest();
  }
  function calculateTotalAmountToRequest(){
    //sum all the amount entered and siplay in one text field and set max value for imprest field
    $(".city_tr").each(function(){
      st_days=parseInt($(this).find('.no_of_days_to_stay').val());
      v=0;
      $(this).find(".stayda").each(function(){
        if(parseFloat($(this).val()) == $(this).val())
          v+=parseFloat($(this).val());
      });
      v=v*st_days;
      $(this).find(".total_stayda").val(v.toFixed(2));
    });
    return ;
    st_days=parseInt($("#no_of_days_to_stay").val());
    v=0;
    $(".stayda").each(function(){
      if(parseFloat($(this).val()) == $(this).val())
        v+=parseFloat($(this).val());
    });
    v=v*st_days;
    $("#total_stayda").val(v.toFixed(2));

  }
    jQuery(document).ready(function(){
    //Date picker
    $(".datepicker").datepicker({
      //startDate: minimumDate,
      endDate: maximumDate,
      autoclose: true,
      orientation: "bottom"
    });


      $('.selectDate').daterangepicker({
          //startDate: 'date("m/d/Y", strtotime($date_from))}}',
          //endDate  : 'date("m/d/Y", strtotime($date_to))}}',
          autoUpdateInput: false, 
          locale: {
              cancelLabel: 'Clear'
          }

        }, function (start, end) {
        }).on('apply.daterangepicker', function(ev, picker) {
          $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
          }).on('cancel.daterangepicker', function(ev, picker) {
          $(this).val('');
      });
      $(".stayda").keyup(function(){
        calculateTotalAmountToRequest();
      });
      $(".amount_to_be").keyup(function(){
        v=0;
        $(".amount_to_be").each(function(){
          //console.log($(this).val());
          if($(this).hasClass('include_cal') && parseFloat($(this).val()) == $(this).val())
            v+=parseFloat($(this).val());
        });
        //$("#total_amount").val(v.toFixed(2));
        //$("#amount_imprest").attr("max", v.toFixed(2));
      });
      
    });
    function GetCityDetails(obj){

      $.ajax({
        type: 'POST',
        url: "{{ url('employees/band-city') }} ",
        data: {city: obj.val(), band: 1},
        success: function(result){
          //$("#rate_per_night").attr('max',result.city_class[0].pivot.price);
          //$("#da").attr('max',result.food_allowance);

          obj.parents('.city_tr').find('.rate_per_night').attr({'max':result.city_class[0].pivot.price, 'placeholder':"Rate/might max "+result.city_class[0].pivot.price});
          obj.parents('.city_tr').find('.da_class').attr({'placeholder':" DA max "+result.food_allowance, "max": result.food_allowance});
        }
      });
    }
    function displayCity(obj, no){
      var stateId = obj.val();
      var stateIds = [];
      stateIds.push(stateId);

      obj.parents('.form-group, .city_tr').children().find(".city_select").empty();
      var displayString = '<option value="">Select city</option>';

      $.ajax({
        type: 'POST',
        url: "{{ url('employees/states-wise-cities') }} ",
        data: {stateIds: stateIds},
        success: function(result){
          if(result.length != 0){
            result.forEach(function(city){
              displayString += '<option value="'+city.id+'">'+city.name+'</option>';
            });
          }else{
            displayString += '<option value="" selected disabled>None</option>';
          }

          //$('#cityId'+no).append(displayString);
          obj.parents('.form-group, .city_tr').children().find(".city_select").html(displayString);
        }
      });
    }
    
  </script>


  @endsection