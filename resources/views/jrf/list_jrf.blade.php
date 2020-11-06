@extends('admins.layouts.app')
@section('content')
<link rel="stylesheet" href="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.css')}}">
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
   <!-- Content Header (Page header) -->
   <section class="content-header">
      <h1>JRF List</h1>
      <ol class="breadcrumb">
         <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
      </ol>
   </section>
   <!-- Main content -->
   <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
         <div class="box">
            <div class="box-header"></div>
              <!-- /.box-header -->
              <div class="box-body">
                   <table id="listHolidays" class="table table-bordered table-striped">
                      <thead class="table-heading-style">
                         <tr>
                            <th>S.No.</th>
                            <th>Designation</th>
                            <th>Role</th>
                            <th>Description</th>
                            <!-- <th>Qualification</th>
                            <th>Skills</th>  -->
                            <th>Created Date</th>
                            <th>Final Status</th>
                            <th>Actions</th>
                            <th>Status</th>
                         </tr>
                      </thead>
                      <tbody>
                         @foreach($jrfs as $key =>$jrf)
                          <tr> 
                            <td>{{$loop->iteration}}</td>
                            <td>{{@$jrf->designation}}</td>
                            <td>{{@$jrf->role}}</td>
                            <td title="{{@$jrf->description}}">
                               @if(strlen($jrf->description) <= 60)
                               {{@$jrf->description}}
                               @else
                               {{substr($jrf->description, 0, 60)}}...
                               @endif
                            </td>

                           <!--  <td>fdg</td>
                            <td>dg</td> -->

                            <td>{{@$jrf->created_at}}</td>
                            <td>
                               @if($jrf->final_status == '0' && $jrf->isactive == 1 && $jrf->secondary_final_status == 'Rejected')
                                 <span class="label label-danger">{{$jrf->secondary_final_status}}</span>
                                 @elseif($jrf->final_status == '0' && $jrf->isactive == 1 && $jrf->secondary_final_status == 'In-Progress')
                                 <span class="label label-warning">{{$jrf->secondary_final_status}}</span>  
                                 @elseif($jrf->final_status == '1' && $jrf->isactive == 1)
                                 <span class="label label-success">{{$jrf->secondary_final_status}}</span>
                                 @elseif($jrf->isactive == 0)
                                 <span class="label" style="background-color: #001f3f;">Cancelled</span>  
                                 @endif
                            </td>
                            <td>
                                @can('edit-jrf')
                                <!-- <a class="btn btn-success" target="_blank" href='{{ url("jrf/edit-jrf/$jrf->id")}}'>Edit</a>  -->
                                @endcan
                                &nbsp;<a class="btn btn-primary" target="_blank" href='{{ url("jrf/view-jrf/$jrf->id")}}'>View</a>
                            </td>
                            <td>
                                @if($jrf->can_cancel_jrf && $jrf->isactive == 1)
                                <a href='{{url("jrf/cancel-jrf/$jrf->id")}}'><span class="label label-danger bg-maroon cancelAppliedLeave" Onclick="return confirm('Are you sure you want to Cancel this JRF request?')">Cancel</span></a>
                                @else
                                <span class="label label-default">None</span>
                                @endif  
                            </td>
                          </tr>
                         @endforeach
                      </tbody>
                      <tfoot class="table-heading-style">
                         <tr>
                            <th>S.No.</th>
                            <th>Designation</th>
                            <th>Role</th>
                            <th>Description</th>
                            <!-- <th>Qualification</th>
                            <th>Skills</th> -->
                            <th>Created Date</th>
                            <th>Final Status</th>
                            <th>Actions</th>
                            <th>Status</th>
                         </tr>
                      </tfoot>
                   </table>
                </div>
              <!-- /.box-body -->
           </div>
           <!-- /.box -->
        </div>
      <!-- /.row -->
    <!-- Main row -->
   </section>
   <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <script src="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
  <script type="text/javascript">
      $(document).ready(function() {
         $('#listHolidays').DataTable({
           scrollX: true,
           responsive: true
         });
      });
  </script>
@endsection