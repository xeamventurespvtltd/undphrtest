@extends('admins.layouts.app')



@section('content')



<!-- Content Wrapper. Contains page content -->

  <div class="content-wrapper">

    <!-- Content Header (Page header) -->

    <section class="content-header">

      <h1>

        User Profile

        <!-- <small>Control panel</small> -->

      </h1>

      <ol class="breadcrumb">

        <li><a href="{{ route('employees.dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>

        <!-- <li class="active">Dashboard</li> -->

      </ol>

    </section>



    <!-- Main content -->

    <section class="content">



      <div class="row">

        <div class="col-md-3">



          <!-- Profile Image -->

          <div class="box box-primary">

            <div class="box-body box-profile">

              <img class="profile-user-img img-responsive img-circle" src="{{@$data['basic']->profile_pic}}" alt="User profile picture">



              <h3 class="profile-username text-center">{{@$data['basic']->full_name}}</h3>



              <p class="text-muted text-center">{{@$data['basic']->designation}}</p>



              <!-- <ul class="list-group list-group-unbordered">

                <li class="list-group-item">

                  <b>Followers</b> <a class="pull-right">1,322</a>

                </li>

                <li class="list-group-item">

                  <b>Following</b> <a class="pull-right">543</a>

                </li>

                <li class="list-group-item">

                  <b>Friends</b> <a class="pull-right">13,287</a>

                </li>

              </ul> -->



            </div>

            <!-- /.box-body -->

          </div>

          <!-- /.box -->



          <!-- About Me Box -->

          <div class="box box-primary">

            <div class="box-header with-border">

              <h3 class="box-title">About User</h3>

            </div>

            <!-- /.box-header -->

            <div class="box-body">

              <strong><i class="fa fa-book margin-r-5"></i> Languages</strong>



              <p class="text-muted">

                @if(!empty(@$data['selectedLanguages']))

                  @foreach($data['selectedLanguages'] as $key => $value)

                    <span class="label label-warning">{{$value}}</span>

                  @endforeach

                @endif

              </p>



              <hr>



              <strong><i class="fa fa-pencil margin-r-5"></i> Skills</strong>



              <p>

                @if(!empty(@$data['selectedSkills']))

                  @foreach($data['selectedSkills'] as $key => $value)

                    <span class="label label-success">{{$value}}</span>

                  @endforeach

                @endif

              </p>



              <hr>



              <strong><i class="fa fa-graduation-cap margin-r-5"></i> Qualifications</strong>



              <p>

                @if(!empty(@$data['selectedQualifications']))

                  @foreach($data['selectedQualifications'] as $key => $value)

                    <span class="label label-danger">{{$value}}</span>

                  @endforeach

                @endif

              </p>



              <hr>



              <strong><i class="fa fa-map-marker margin-r-5"></i> Location</strong>



              <p class="text-muted">{{@$data['profileTab']->location_name}}</p>



            </div>

            <!-- /.box-body -->

          </div>

          <!-- /.box -->

        </div>

        <!-- /.col -->

        <div class="col-md-9">

          <div class="nav-tabs-custom">

            <ul class="nav nav-tabs edit-nav-styling">

              <li id="basicDetailsTab" class="active"><a href="#tab_basicDetailsTab" data-toggle="tab">Basic Details</a></li>

              <li id="profileDetailsTab"><a href="#tab_profileDetailsTab" data-toggle="tab">Project Details</a></li>


            </ul>

            <div class="tab-content">

              <div class="active tab-pane" id="tab_basicDetailsTab">

                    

                      <div class="box-body no-padding">

                        <table class="table table-striped table-bordered">

                          <tr>

                            <th style="width: 30%">Field</th>

                            <th style="width: 70%">Value</th>

                          </tr>

                          

                          <tr>

                            <td><em>Employee Name</em></td>

                            <td>

                              {{@$data['basic']->full_name}}

                            </td>

                          </tr>



                          



                         



                          <tr>

                            <td><em>Employee User Id</em></td>

                            <td>

                              {{@$data['basic']->emp_xeam_code}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Email</em></td>

                            <td>

                              {{@$data['basic']->email}}

                            </td>

                          </tr>



                       



                          <tr>

                            <td><em>Mobile</em></td>

                            <td>

                              {{@$data['basic']->mobile}}

                            </td>

                          </tr>





                          <tr>

                            <td><em>Birth Date</em></td>

                            <td>

                              {{@$data['basic']->birth_date}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Joining Date</em></td>

                            <td>

                              {{@$data['basic']->joining_date}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Gender</em></td>

                            <td>

                              {{@$data['basic']->gender}}

                            </td>

                          </tr>

                         

                        </table>

                      </div>

                      

              </div>

              <!-- /.tab-pane -->

              <div class="tab-pane" id="tab_profileDetailsTab">

                <div class="box-body no-padding">

                        <table class="table table-striped table-bordered">

                          <tr>

                            <th style="width: 30%">Field</th>

                            <th style="width: 70%">Value</th>

                          </tr>

                          

                          <tr>

                            <td><em>Department</em></td>

                            <td>

                              {{@$data['basic']->department_name}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Designation</em></td>

                            <td>

                              {{@$data['basic']->designation}}

                            </td>

                          </tr>




                          <tr>

                            <td><em>Location</em></td>

                            <td>

                              {{@$data['profileTab']->location_name}}

                            </td>

                          </tr>



                         



                        








                          

                        </table>

                      </div>

              </div>

              <!-- /.tab-pane -->



              <div class="tab-pane" id="tab_documentDetailsTab">

                <div class="box-body no-padding">

                        <table class="table table-striped table-bordered">

                          <tr>

                            <th style="width: 10%">#</th>

                            <th style="width: 30%">Type</th>

                            <th style="width: 30%">Uploaded</th>

                            <th style="width: 30%">File</th>

                          </tr>

                          

                          <?php $counter = 0;?> 

                            @foreach($data['documents'] as $document)

                            <tr>

                              <td>{{++$counter}}</td>

                              <td><em>{{$document->document_type_name}}</em></td>

                              <td>

                                @if(empty($document->file_name))

                                  <span class="badge bg-red">No</span>

                                @else

                                  <span class="badge bg-green">Yes</span>

                                @endif

                              </td>

                              <td>

                                @if(!empty($document->file_name))

                                  <span><a target="_blank" href="{{config('constants.uploadPaths.document').$document->file_name}}"><i class="fa fa-file-text-o" aria-hidden="true"></i></a></span>

                                @endif

                              </td>

                            </tr>

                            @endforeach

                          

                        </table>

                      </div>

              </div>

              <!-- /.tab-pane -->



              <div class="tab-pane" id="tab_addressDetailsTab">

                <div class="box-body no-padding">

                        <table class="table table-striped table-bordered">

                          <tr>

                            <th style="width: 30%">Present Address :-</th>

                            <th style="width: 70%"></th>

                          </tr>

                          

                          <tr>

                            <td><em>House Number</em></td>

                            <td>

                              {{@$data['presentAddress']->house_no}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Road/Street</em></td>

                            <td>

                              {{@$data['presentAddress']->road_street}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Locality/Area</em></td>

                            <td>

                              {{@$data['presentAddress']->locality_area}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Pincode</em></td>

                            <td>

                              {{@$data['presentAddress']->pincode}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Country</em></td>

                            <td>

                              {{@$data['presentAddress']->c_name}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>State</em></td>

                            <td>

                              {{@$data['presentAddress']->s_name}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>City</em></td>

                            <td>

                              {{@$data['presentAddress']->ct_name}}

                            </td>

                          </tr>

                          

                        </table>

                      </div>

                      <br>

                      <div class="box-body no-padding">

                        <table class="table table-striped table-bordered">

                          <tr>

                            <th style="width: 30%">Permanent Address :-</th>

                            <th style="width: 70%"></th>

                          </tr>

                          

                          <tr>

                            <td><em>House Number</em></td>

                            <td>

                              {{@$data['permanentAddress']->house_no}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Road/Street</em></td>

                            <td>

                              {{@$data['permanentAddress']->road_street}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Locality/Area</em></td>

                            <td>

                              {{@$data['permanentAddress']->locality_area}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Pincode</em></td>

                            <td>

                              {{@$data['permanentAddress']->pincode}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Country</em></td>

                            <td>

                              {{@$data['permanentAddress']->c_name}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>State</em></td>

                            <td>

                              {{@$data['permanentAddress']->s_name}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>City</em></td>

                            <td>

                              {{@$data['permanentAddress']->ct_name}}

                            </td>

                          </tr>

                          

                        </table>

                      </div>



              </div>

              <!-- /.tab-pane -->



              <div class="tab-pane" id="tab_accountDetailsTab">

                <div class="box-body no-padding">

                        <table class="table table-striped table-bordered">

                          <tr>

                            <th style="width: 30%">Field</th>

                            <th style="width: 70%">Value</th>

                          </tr>

                          

                          <tr>

                            <td><em>Adhaar Number</em></td>

                            <td>

                              {{@$data['accountInfo']->adhaar}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>PAN Number</em></td>

                            <td>

                              {{@$data['accountInfo']->pan_no}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Employee ESI Number</em></td>

                            <td>

                              {{@$data['accountInfo']->emp_esi_no}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Employee previous ESI Number</em></td>

                            <td>

                              {{@$data['accountInfo']->emp_prev_esi_no}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Employee Dispensary</em></td>

                            <td>

                              {{@$data['accountInfo']->emp_dispensary}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>PF Number for department file</em></td>

                            <td>

                              {{@$data['accountInfo']->pf_no_department}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>UAN Number</em></td>

                            <td>

                              {{@$data['accountInfo']->uan_no}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Previous UAN Number</em></td>

                            <td>

                              {{@$data['accountInfo']->prev_uan_no}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Bank Name</em></td>

                            <td>

                              {{@$data['accountInfo']->financial_institution_name}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Account Holder Name</em></td>

                            <td>

                              {{@$data['accountInfo']->acc_holder_name}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Bank Account Number</em></td>

                            <td>

                              {{@$data['accountInfo']->bank_acc_no}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>IFSC Code</em></td>

                            <td>

                              {{@$data['accountInfo']->ifsc}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Contract Signed</em></td>

                            <td>

                              @if(@$data['basic']->contract_signed == '1')

                              {{"Yes"}}

                              @else

                              {{"No"}}

                              @endif

                            </td>

                          </tr>



                          @if(@$data['basic']->contract_signed == '1')

                            <tr>

                              <td><em>Contract Signed Date</em></td>

                              <td>

                                {{@$data['basic']->contract_signed_date}}

                              </td>

                            </tr>

                          @endif

                          

                        </table>

                      </div>

              </div>

              <!-- /.tab-pane -->



              <div class="tab-pane" id="tab_referenceDetailsTab">

                <div class="box-body no-padding">

                        <table class="table table-striped table-bordered">

                          <tr>

                            <th style="width: 30%">Reference 1 :-</th>

                            <th style="width: 70%"></th>

                          </tr>

                          

                          <tr>

                            <td><em>Name</em></td>

                            <td>

                              {{@$data['reference1']->reference_name}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Address</em></td>

                            <td>

                              {{@$data['reference1']->reference_address}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Phone Number</em></td>

                            <td>

                              {{@$data['reference1']->reference_phone}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Email</em></td>

                            <td>

                              {{@$data['reference1']->reference_email}}

                            </td>

                          </tr>

                          

                        </table>

                  </div>



                  <br>



                  <div class="box-body no-padding">

                        <table class="table table-striped table-bordered">

                          <tr>

                            <th style="width: 30%">Reference 2 :-</th>

                            <th style="width: 70%"></th>

                          </tr>

                          

                          <tr>

                            <td><em>Name</em></td>

                            <td>

                              {{@$data['reference2']->reference_name}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Address</em></td>

                            <td>

                              {{@$data['reference2']->reference_address}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Phone Number</em></td>

                            <td>

                               {{@$data['reference2']->reference_phone}}

                            </td>

                          </tr>



                          <tr>

                            <td><em>Email</em></td>

                            <td>

                              {{@$data['reference2']->reference_email}}

                            </td>

                          </tr>

                          

                        </table>

                  </div>



              </div>

              <!-- /.tab-pane -->



            </div>

            <!-- /.tab-content -->

          </div>

          <!-- /.nav-tabs-custom -->

        </div>

        <!-- /.col -->

      </div>

      <!-- /.row -->



    </section>

    <!-- /.content -->

  </div>

  <!-- /.content-wrapper -->



  @endsection