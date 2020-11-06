@extends('admins.layouts.app')

@section('content')

<link rel="stylesheet" href="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.css')}}">
<link rel="stylesheet" href="{{asset('public/admin_assets/plugins/jquery-toast/jquery.toast.min.css')}}">
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>List Leads</h1>
    <ol class="breadcrumb">
      <li>
        <a href="{{ url('employees/dashboard') }}">
          <i class="fa fa-dashboard"></i> Home
        </a>
      </li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row">
      <div class="box">            
        @include('admins.validation_errors')
        <!-- /.box-header -->
        <div class="box-body">
          <div class="box-title">
            <div class="row">
              <div class="col-md-12">
                @php
                  $inputs = Request::all();
                @endphp
                <form id="lead_list_form" method="GET">
                  <div class="col-md-3 form-group row">
                    <label>Lead Type</label>
                    <select class="form-control input-sm basic-detail-input-style" name="lead_type" id="lead_type">
                      <option value="all" @if($leadType == 'all') selected @endif>All Leads</option>
                      <option value="created" @if($leadType == 'created') selected @endif>Created Leads</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <button type="submit" class="btn searchbtn-attendance">Search <i class="fa fa-search"></i></button>
                      <div class="clearfix">&nbsp;</div>
                    </div>
                  </div>
                  <div class="col-md-3 pull-right text-right row">
                    <h3 class="box-title">
                      <a class="btn btn-info" href="{!! route('leads-management.create') !!}">Add</a>
                    </h3>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="table-responsive col-md-12">
              <table id="leads-table" class="table table-bordered table-striped table-condensed table-responsive">
                <thead class="table-heading-style">
                  <tr>
                    <th class="no-sort"> S.No. </th>
                    <th>ULN</th>
                    <th>Name of prospect</th>
                    <th>Business Type</th>
                    <th>Assignee</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th class="no-sort text-center">
                      Actions
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </th>
                  </tr>
                </thead>
                <tbody>
                  @if(!empty($leadList) && count($leadList) > 0)
                          
                    @php
                      $fileAbsolutePath = \Config::get('constants.uploadPaths.leadDocuments');
                      $filePath = asset('public') . \Config::get('constants.uploadPaths.leadDocumentPath');
                      $businessTypeArr = [1 => 'Govt. Business', 2 => 'Corporate Business', 3 => 'International Business'];

                      $statusArr = [
                        1 => 'New', 2 => 'Open', 
                        3 => 'Complete', 4 => 'Rejected by Hod', 
                        5 => 'Closed', 6 => 'Abandoned'
                      ];

                      $priorityArr = [0=> 'Low', 1=> 'Normal', 2=> 'Critical'];
                    @endphp

                    @foreach($leadList as $key => $lead)
                      <tr class="lead-row @if(in_array($lead->status, [4, 6])) tr-danger @endif">
                        <td>{!! $loop->iteration !!}</td>
                        <td>{!! $lead->lead_code !!}</td>
                        <td>
                          <div title="{!! $lead->name_of_prospect ?? '--' !!}">
                            @if($lead->name_of_prospect)
                              @php
                                echo (strlen($lead->name_of_prospect)>25)? substr($lead->name_of_prospect, 0, 25).'.....' : $lead->name_of_prospect ;
                              @endphp
                            @else
                              --
                            @endif
                          </div>
                        </td>
                        <td>{!! $businessTypeArr[$lead->business_type] ?? '--' !!}</td>
                        <td>
                          @if(isset($lead->leadExecutives->fullname))
                            {!! trim($lead->leadExecutives->fullname) !!}
                          @else
                            --
                          @endif
                        </td>
                        <td>{!! $statusArr[$lead->status] ?? '--' !!}</td>
                        <td>
                          @php
                            $priority = $priorityArr[$lead->priority];
                          @endphp

                          @if($lead->priority == 2)
                            <span class="label label-danger">{!! $priority !!}</span>
                          @elseif($lead->priority == 1)
                            <span class="label label-warning">{!! $priority !!}</span>
                          @else
                            <span class="label label-info">{!! $priority !!}</span>
                          @endif
                        </td>
                        <td class="text-center">
                          @if(!empty($lead->file_name) && file_exists($fileAbsolutePath . $lead->file_name))
                            <a href="{!! $filePath . $lead->file_name !!}" class="btn btn-info btn-xs" target="_blank" title="Download Attachment">
                              <i class="fa fa-download"></i>
                            </a>
                          @endif
                        
                          <a href="{!! route('leads-management.view', $lead->id) !!}" class="btn btn-primary btn-xs" title="View">
                            <i class="fa fa-eye"></i>
                          </a>

                          @if(!empty($lead->status) && $lead->status != 6)
                          <a href="javascript:void(0)" class="btn btn-danger btn-xs reject-lead" title="Reject Lead" data-url="{!! route('leads-management.reject-lead', $lead->id) !!}">
                            <i class="fa fa-trash"></i>
                          </a>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot class="table-heading-style">
                  <tr>
                    <th>S.No.</th>
                    <th>ULN</th>
                    <th>Name of prospect</th>
                    <th>Business Type</th>
                    <th>Assignee</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
    <!-- /.row -->
    <!-- /.modal -->
    <!-- /.row (main row) -->
  </section>
  <!-- /.content-wrapper -->
</div>
@endsection

@section('script')
<script src="{!! asset('public/admin_assets/plugins/sweetalert/sweetalert.min.js') !!}"></script>
<script src="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
<script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>
<script src="{{asset('public/admin_assets/plugins/jquery-toast/jquery.toast.min.js')}}"></script>

<script type="text/javascript"> 
$(document).ready(function () {

  $('#leads-table').DataTable({
    processing: true,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
    pageLength: -1,
    columnDefs: [
      {
        targets: 'no-sort',
        orderable: false,
      }
    ],
    aaSorting: []
  });

  $(document).on('click', '#leads-table-form .reset_btn', function(event) {
    event.preventDefault(); event.stopPropagation();
    $('#leads-table-form').trigger('reset');
  });

  $(document).on('click', '.reject-lead', function(event) {
    event.preventDefault(); event.stopPropagation();
    rejectLead($(this));
  });
});

function rejectLead(obj) {

  if(typeof $(obj).data('url') != 'undefined') {
    var data_url  = $(obj).data('url');

    swal({
      title: "Are you sure?",
      text: "You will not be able to recover this record!",
      icon: "warning",
      buttons: [
        'No, cancel it!',
        'Yes, I am sure!'
      ],
      dangerMode: true,
    }).then(function(isConfirm) {
      if (isConfirm) {
        $.ajax({
          url: data_url,
          type: "POST",
          success: function (res) {
            if(res.status == 1) {
              swal("Done!", res.msg, "success");

              $(obj).addClass('hide');
              $(obj).parents('.lead-row').addClass('tr-danger');
              
              setTimeout(function() {
                window.location.reload();
              }, 1000);
            } else {
              swal("Error:", res.msg, "error");
            } 
          },
          error: function (xhr, ajaxOptions, thrownError) {
            var xhrRes = xhr.responseJSON;
            if(xhrRes.status == 401) {
              swal("Error Code: " + xhrRes.status, xhrRes.msg, "error");
            } else {
              swal("Error deleting!", "Please try again", "error");
            }
          }
        });
      }
    });
  }
}
</script>
@endsection