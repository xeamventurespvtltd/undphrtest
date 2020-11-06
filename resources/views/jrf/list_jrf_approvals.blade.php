@extends('admins.layouts.app') @section('content')
<style type="text/css">
  #status_check {
  background-color: #ff0000 !important;
}
</style>
<link rel="stylesheet" href="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.css')}}">
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
   <!-- Content Header (Page header) -->
   <section class="content-header">
      <h1>JRF Approval List</h1>
      <ol class="breadcrumb">
        <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
      </ol>
   </section>
   <!-- Main content -->
   <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
         <div class="box">
            <!-- <div class="box-header">
               </div> -->
            <!-- /.box-header -->
            @include('admins.validation_errors')
            <div class="box-body">
               <div class="dropdown">
                  <button class="btn btn-warning dropdown-toggle" type="button" data-toggle="dropdown">
                  {{@$selected_status}}
                  <span class="caret"></span></button>
                  <ul class="dropdown-menu">
                     <li><a href='{{url("jrf/approve-jrf/pending")}}'>Pending</a></li>
                     <li><a href='{{url("jrf/approve-jrf/assigned")}}'>Assigned</a></li>
                     <li><a href='{{url("jrf/approve-jrf/closed")}}'>Closed</a></li>
                     <li><a href='{{url("jrf/approve-jrf/rejected")}}'>Rejected</a></li>
                  </ul>
               </div>

            <table id="listLeaveApproval" class="table table-bordered table-striped" style="height:150px;">
              <thead class="table-heading-style">
                 <tr>
                    <th>S.No.</th>
                    <th>Jrf Created By</th>
                    <th>Job Role</th>
                    <th>Job Designation</th>
                    <th>No. of Position</th>
                    <th>Salary Range</th>
                    <th>Experience</th>
                    <th>JRF Status</th>
                    <!-- <th>JRF Assignment</th> -->
                    <th>Status</th>
                    <th>Action</th>
                 </tr>
              </thead>
            <tbody>
            @if(!@$data->isEmpty())
              @foreach($data as $key =>$value)
              <tr>
                <td>{{@$loop->iteration}}</td>
                <td>{{@$value->jrf_creater_name}}</td>
                <td><a href='{{ url("jrf/view-jrf/$value->jrf_id")}}' class="additionalLeaveDetails" title="more details">{{@$value->role}}</a></td>
                <td>{{@$value->designation}}</td>
                <td>{{@$value->number_of_positions}}</td>
                <td>{{@$value->salary_range}}</td>
                <td>{{@$value->experience}}</td>
                <td>
                  @if($value->jrf_status == '0' && $value->secondary_final_status == 'In-Progress')
                  <span class="label label-success">{{$value->secondary_final_status}}</span> 
                  @elseif($value->jrf_status == '1' && $value->final_status == 0 && $value->secondary_final_status == 'assigned')
                  <span class="label label-warning">{{$value->secondary_final_status}}</span> 
                  @elseif($value->jrf_status == '2' && $value->secondary_final_status = 'Rejected')
                  <span class="label label-danger" id="status_check">                               {{$value->secondary_final_status}}</span> 
                  @elseif($value->jrf_status == '3' && $value->final_status == 1 && $value->secondary_final_status = 'closed')
                  <span class="label label-info"> 
                  {{$value->secondary_final_status}}</span> 
                  @endif
                </td>
                <td>
                    @can('edit-jrf')
                    <a class="btn btn-success" target="_blank" href='{{ url("jrf/edit-jrf/$value->jrf_id")}}'>Edit</a> 
                    @endcan
                    <a class="btn btn-primary" target="_blank" href='{{ url("jrf/view-jrf/$value->jrf_id")}}'>View</a>
                </td>
                <td>
                  <div class="dropdown">
                    @if($value->final_status == '0' && $value->jrf_status == '0')
                    <button class="btn btn-warning dropdown-toggle" type="button" data-toggle="dropdown">{{"None"}} 

                    @elseif($value->final_status == '1')</button>
                    <button class="btn btn-success dropdown-toggle" type="button" data-toggle="dropdown">
                    {{"Closed"}} 

                    @elseif($value->final_status == '0' &&  $value->jrf_status == '1')</button>
                    <button class="btn btn-success dropdown-toggle" type="button" data-toggle="dropdown">
                    {{"Assigned"}} 

                    @elseif($value->final_status == '0' && $value->jrf_status == '2')</button>
                    <button class="btn btn-danger dropdown-toggle" type="button" data-toggle="dropdown">
                    {{"Rejected"}} @endif
                    <span class="caret"></span></button>

                    @if($value->final_status == '0' && $value->jrf_status == '0')
                    <ul class="dropdown-menu">
                      <li>
                        <a href='{{ url("jrf/edit-jrf/$value->jrf_id")}}' class="">Approve</a>
                      </li>

                      @if(!empty($value->hierarchy_user_id))
                      <li>
                        <a href='javascript:void(0)' class="approvalStatus" data-user_id="{{@$value->supervisor_id}}" data-jrf_id="{{@$value->jrf_id}}" data-statusname="Rejected" data-final_status="2">Reject</a>
                      </li>
                      @endif
                    </ul>
                    @endif
                  </div>
                </td>
              </tr>
              @endforeach  

              @else
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td>No data available</td>
                  <td></td>
                  <td></td>
                  <td></td>  
                  <td></td>
                </tr>
              @endif
              </tbody>
                <tfoot class="table-heading-style">
                   <tr>
                      <th>S.No.</th>
                      <th>Jrf Created By</th>
                      <th>Job Role</th>
                      <th>Job Designation</th>
                      <th>No. of Position</th>
                      <th>Salary Range</th>
                      <th>Experience</th>
                      <th>JRF Status</th>
                      <!-- <th>Approval Status</th> -->
                      <th>Status</th>
                      <th>Action</th>
                   </tr>
                </tfoot>
            </table>
          </div>
        <!-- /.box-body -->
      </div>
    <!-- /.box -->
  </div><!-- /.row -->
<!-- Main row -->
</section>
<!-- /.content -->
   <!-- for Rejection -->
     <div class="modal fade" id="jrfsStatusModal">
       <div class="modal-dialog">
          <div class="modal-content">
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Rejection Status Form</h4>
             </div>
             <div class="modal-body">
                <form id="jrfStatusForm" action="{{url('jrf/save-jrf-rejection') }}" method="POST" enctype="multipart/form-data">
                  {{ csrf_field() }}
                  <div class="box-body">
                    <div class="form-group">
                       <label for="statusName" class="docType">Selected Status</label>
                       <input type="text" class="form-control" id="statusName" name="statusName" value="" readonly>
                    </div>
                    <input type="hidden" name="jrf_id" id="jrf_id">
                    <input type="hidden" name="userId" id="userId">
                    <input type="hidden" name="final_status" id="final_status">
                    <div class="form-group">
                       <label for="rejection_reason">Remark</label>
                       <textarea class="form-control" rows="5" name="rejection_reason" id="rejection_reason"></textarea>
                    </div>
                  </div>
                  <!-- /.box-body -->
                  <br>
                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary" id="jrfStatusFormSubmit">Submit</button>
                  </div>
              </form>
          </div>
        </div>
      </div>
    </div>
  <!-- end of rejection -->
  </div>
<!-- /.content-wrapper -->
<script src="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
  <script type="text/javascript">
      $(document).ready(function() {
         $(".approvalStatus").on('click', function() {
             var jrf_id = $(this).data("jrf_id");
             var userId = $(this).data("user_id");
             var final_status = $(this).data("final_status");
             var statusname = $(this).data("statusname");
             $("#jrf_id").val(jrf_id);
             $("#userId").val(userId);
             $("#final_status").val(final_status);
             $("#statusName").val(statusname);
             $('#jrfsStatusModal').modal('show');
          });

           $("#jrfStatusForm").validate({
             rules: {
                 "remark": {
                     required: true,
                 }
             },
             messages: {
                 "remark": {
                     required: 'Please enter a remark.',
                 }
             }
          });
      });
  </script>

<script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
<script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>
@endsection