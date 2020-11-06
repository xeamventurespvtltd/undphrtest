@extends('admins.layouts.app')

@section('content')
<link rel="stylesheet" href="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.css')}}">
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        Travel Approval Requests
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
      </ol>
    </section>
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
          <div class="box">
            <div class="box-header">
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                <div class="col-md-3">
                  <select class="form-control" onchange="window.location.href='{{url('/travel/approval-requests?filter_status=')}}'+this.value">
                    <option @if($data['filter_status']=='new') selected="" @endif value="new">Only New Requests</option>
                    <option @if($data['filter_status']=='discussion') selected="" @endif value="discussion">Only Discussion Requests</option>
                    <option  @if($data['filter_status']=='discarded') selected="" @endif value="discarded">Only Discarded Requests</option>
                    <option  @if($data['filter_status']=='approved') selected="" @endif value="approved">Only Approved Requests</option>
                  </select>
                </div>
              </div>
              <div class="row"><div class="col-md-12">&nbsp;</div></div>
              <div class="row">
                <div class="col-md-12">
                  <table id="" class="table table-bordered table-striped">
                    <thead class="table-heading-style">
                    <tr>
                      <th>S.No.</th>
                      <th>Name</th>
                      <th>From</th>
                      <th>To</th>
                      <th>Dates</th>
                      <th>View</th>
                    </tr>
                    </thead>
                    <tbody>
                      @if($data['approvals']->count())
                    @foreach($data['approvals'] as $approval)  
                    <tr>
                      <td>{{$loop->iteration}}.</td>
                      <td>{{$approval->user->employee->salutation}}{{$approval->user->employee->fullname}}</td>
                      <td>{{$approval->city_from->name}} ({{$approval->city_from->state->name}})</td>
                      <td>{{$approval->city_to->name}} ({{$approval->city_to->state->name}})</td>
                      <td>{{formatDate($approval->date_from)}} <br> {{formatDate($approval->date_to)}}</td>
                      <td>
                        <a href="{{url('travel/approval-request-details/'.encrypt($approval->id))}}" class="btn btn-xs btn-success" >View Request</a>
                        @if($approval->status=='approved')
                          @if($approval->claims && isset($approval->claims->status) && $approval->claims->status!='back')
                          <a href="{{url('travel/claim-view/'.encrypt($approval->id))}}" class="btn btn-xs btn-info" >View Claim</a>
                          @else

                          <a href="{{url('travel/claim-form/'.encrypt($approval->id))}}" class="btn btn-xs @if($approval->claims && $approval->claims->status=='back') btn-danger @else btn-info @endif" > @if($approval->claims && $approval->claims->status=='back') Update @endif Claim Form </a>
                          @endif
                        @endif
                      </td>
                    </tr>
                    @endforeach
                      @else
                    <tr>
                      <td colspan="6">
                        No record found
                      </td>
                    </tr>
                      @endif
                    </tbody>
                    <tfoot class="table-heading-style">
                    <tr>
                      <th>S.No.</th>
                      <th>Name</th>
                      <th>From</th>
                      <th>To</th>
                      <th>Dates</th>
                      
                      <th>View</th>
                    </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>
      </div>
    </section>
    
  </div>
  <script src="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
  <script type="text/javascript">
      $(".approveBtn").on('click',function(){
        if (!confirm("Are you sure you want to approve this company?")) {
            return false; 
        }
      });

      $(".additionalCompanyInfo").on('click',function(){
        var companyId = $(this).data('companyid');

        $.ajax({
          type: "POST",
          url: "{{ url('mastertables/additional-company-info') }}",
          data: {company_id: companyId},
          success: function (result){
            $(".companyInfoModalBody").html(result);
            $('#companyInfoModal').modal('show');

          }
        });
      });

      $(document).ready(function() {

          $('#listRegisterCompanies').DataTable({
            scrollX: true,
            responsive: true
          });

      });

  </script>

  @endsection