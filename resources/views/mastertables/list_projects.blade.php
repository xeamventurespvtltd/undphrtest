@extends('admins.layouts.app')

@section('content')

<link rel="stylesheet" href="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.css')}}">

<!-- Content Wrapper. Contains page content -->

  <div class="content-wrapper">

    <!-- Content Header (Page header) -->

    <section class="content-header">

      <h1>
        Projects List
        <!-- <small>Control panel</small> -->
      </h1>

      <ol class="breadcrumb">
        <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
      </ol>

    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title"><a class="btn btn-info" href='{{ url("mastertables/projects/add") }}'>Add</a></h3>
            </div>

            <!-- /.box-header -->

            <div class="box-body">

              <table id="listProjects" class="table table-bordered table-striped">

                <thead class="table-heading-style">

                <tr>

                  <th>S.No.</th>

                  <th>Project Name</th>

                  <th>Created By</th>

                  <th>Approval By</th>

                  <th>Company Name</th>

                  <th>Approval Status</th>

                  @if(auth()->user()->can('edit-project') || auth()->user()->can('approve-project'))

                  <th style="width: 70px;">Actions</th>

                  @endif



                  @can('create-project')

                  <th>Status</th>

                  @endcan

                </tr>

                </thead>

                <tbody>

                <?php $counter = 0; ?>  

                @foreach($projects as $key =>$value)  

                <tr>

                  <td>{{$loop->iteration}}</td>

                  <td><a href="javascript:void(0)" class="additionalProjectInfo" data-projectid="{{$value->id}}" title="more details">{{$value->name}}</a></td>

                  <td>{{@$value->creator->employee->fullname}}</td>

                  <td>{{@$value->approval->approver->employee->fullname}}</td>

                  <td>{{$value->company->name}}</td>

                  <td>

                    @if($value->approval_status == '0')

                      <span class="label label-danger">Not Approved</span>

                    @else

                      <span class="label label-success">Approved</span>

                    @endif

                  </td>

                  @if(auth()->user()->can('edit-project') || auth()->user()->can('approve-project'))

                  <td>

                    @if(auth()->user()->can('edit-project'))

                    <a class="btn bg-purple" href='{{ url("mastertables/projects/edit/$value->id")}}' title="edit"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>&nbsp;

                    @endif



                    @if(auth()->user()->can('approve-project') && $value->approval_status == '0')

                    <a class="btn bg-navy approveBtn" href='{{ url("mastertables/projects/approve/$value->id")}}' title="approve"><i class="fa fa-check" aria-hidden="true"></i></a>

                    @endif

                  </td>

                  @endif

                  @can('create-project')

                  <td>

                        <div class="dropdown">

                            @if($value->isactive)

                            <button class="btn btn-warning dropdown-toggle" type="button" data-toggle="dropdown">

                             {{"Active"}}

                            @else

                            <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">

                             {{"Inactive"}}

                            @endif  

                          <span class="caret"></span></button>

                          <ul class="dropdown-menu">

                            <li>

                                @if($value->isactive)

                                  <a href='{{ url("mastertables/projects/deactivate/$value->id")}}'>De-activate</a>

                                @else

                                  <a href='{{ url("mastertables/projects/activate/$value->id")}}'>Activate</a>

                                @endif

                            </li>

                            

                          </ul>

                        </div>

                  </td>

                  @endcan

                </tr>

                @endforeach

                </tbody>

                <tfoot class="table-heading-style">

                <tr>

                  <th>S.No.</th>

                  <th>Project Name</th>

                  <th>Created By</th>

                  <th>Approval By</th>

                  <th>Company Name</th>

                  <th>Approval Status</th>

                   @if(auth()->user()->can('edit-project') || auth()->user()->can('approve-project'))

                  <th>Actions</th>

                  @endif



                  @can('create-project')

                  <th>Status</th>

                  @endcan

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

      <div class="row">

        <!-- Left col -->

        

      </div>

      <!-- /.row (main row) -->



    </section>

    <!-- /.content -->



    <div class="modal fade" id="projectInfoModal">

      <div class="modal-dialog">

        <div class="modal-content">

          <div class="modal-header">

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">

              <span aria-hidden="true">&times;</span></button>

            <h4 class="modal-title">Additional Information</h4>

          </div>

          <div class="modal-body projectInfoModalBody">

              

          </div>

          

        </div>

        <!-- /.modal-content -->

      </div>

    <!-- /.modal-dialog -->

    </div>

      <!-- /.modal -->

  </div>

  <!-- /.content-wrapper -->



  <script src="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.js')}}"></script>

  <script type="text/javascript">

      $(".approveBtn").on('click',function(){
        if (!confirm("Are you sure you want to approve this project?")) {
            return false; 
        }
      });

      $(".additionalProjectInfo").on('click',function(){
        var projectId = $(this).data('projectid');

        $.ajax({
          type: "POST",
          url: "{{ url('mastertables/additional-project-info') }}",
          data: {project_id: projectId},
          success: function (result){
            $(".projectInfoModalBody").html(result);
            $('#projectInfoModal').modal('show');
          }
        });
      });

      $(document).ready(function() {
          $('#listProjects').DataTable({
            scrollX: true,
            responsive: true
          });
      });

  </script>

  @endsection