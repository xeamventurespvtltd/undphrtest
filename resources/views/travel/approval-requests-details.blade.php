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
        <li><a href="{{ url('travel/approval-requests') }}"><i class="fa fa-sitemap"></i> Travel Approval Requests</a></li>
      </ol>
    </section>
    
    <section class="content">
    @include('admins.validation_errors')
      <!-- Small boxes (Stat box) -->
      <div class="row">
          <div class="box">
            <div class="box-header">
              <h3>{{$approval->user->employee->salutation}}{{$approval->user->employee->fullname}}</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                <div class="col-md-12">
                    <h4 class="modal-title">{{$approval->city_from->name}} ({{$approval->city_from->state->name}}) to {{$approval->city_to->name}} ({{$approval->city_to->state->name}})</h4>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <table class="table table-bordered table-striped">
                    <tr>
                      <td colspan="2">
                        <legend>Travel Details</legend>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        Dates of travel: {{formatDate($approval->date_from)}}  to  {{formatDate($approval->date_to)}}
                      </td>
                      <td>
                        For project: {{implode(",", $approval->project->pluck('name')->toArray())}}
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2">
                        Purpose: {{$approval->purpose}}
                      </td>
                    </tr>
                    <tr>
                      <td>
                        Travel conveyance: {{implode(",", $approval->conveyance_travel->pluck('name')->toArray())}}
                      </td>
                      <td>
                        Travel conveyance amount: {{moneyFormat($approval->expected_amount)}}

                      </td>
                    </tr>
                    <tr>
                      <td>
                        Local conveyance: {{implode(",", $approval->conveyance_local->pluck('name')->toArray())}}
                      </td>
                      <td>
                        Local conveyance amount: {{moneyFormat($approval->expected_amount_local)}}
                        @php $grand_total=$approval->expected_amount+$approval->expected_amount_local @endphp
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2">
                        Under Policy: @if($approval->under_policy) Yes @else No @endif
                      </td>
                    </tr>
                    @php $total=0; @endphp
                    @if($approval->stay->count())
                    <tr>
                      <td colspan="2">
                        <legend>Stay Details</legend>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2">
                        <table class="table table-bordered table-striped">
                          <thead>
                            <tr>
                              <th>S.No</th>
                              <th>Dates</th>
                              <th>City [Class] State</th>
                              <th class="text-right">Rate/night</th>
                              <th class="text-right">Max rate/night</th>
                              <th class="text-right">DA</th>
                              <th class="text-right">Max DA [ {{$approval->user->designation[0]->band->name}} ]</th>
                              <th class="text-right">Total</th>
                            </tr>
                          </thead>
                          <tbody id="">
                            
                            @foreach($approval->stay as $stay)
                            @php
                              $total+=$subtotal=($stay->rate_per_night*claculateNightsTwoDates($stay->from_date, $stay->to_date))+$stay->da;
                            @endphp
                            <tr>
                              <td>{{$loop->iteration}}.</td>
                              <td>
                                {{formatDate($stay->from_date)}} to {{formatDate($stay->to_date)}}
                              </td>
                              <td>
                                {{$stay->city->name}} [ {{$stay->city->city_class->name}} ] ({{$stay->city->state->name}})
                              </td>
                              
                              <td class="text-right">{{moneyFormat($stay->rate_per_night)}} 
                                @php
                                $band_city_class=getBandCityClassDetails($approval->user->designation[0]->band->id, $stay->city->city_class->id);
                                @endphp
                              </td> 
                              <td class="text-right">
                                {{moneyFormat($band_city_class->price)}}
                              </td>
                              <td class="text-right">{{moneyFormat($stay->da)}}</td> 
                              <td class="text-right">{{moneyFormat($approval->user->designation[0]->band->food_allowance)}}</td> 
                              <td class="text-right">{{moneyFormat($subtotal)}}</td> 
                            </tr>
                            @endforeach
                            <tr>
                              <td colspan="7" class="text-bold">Total</td>
                              <td class="text-right text-bold" >{{moneyFormat($total)}}</td>
                            </tr>
                            @php $grand_total+=$total; @endphp
                          </tbody>
                        </table>
                      </td>
                    </tr>
                    @endif
                    @if(isset($approval->other_approval->id))
                    <tr>
                      <td colspan="2">
                        <legend>Other financial approvals</legend>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        For project: {{implode(",", $approval->other_approval->project->pluck('name')->toArray())}}
                      </td>
                      <td>
                        {{$approval->other_approval->city->name}} ({{$approval->other_approval->city->state->name}})
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2">
                        Purpose: {{$approval->other_approval->purpose}}
                      </td>
                    </tr>
                    <tr>
                      <td>Amount</td>
                      <td class="text-right">
                        {{moneyFormat($approval->other_approval->amount)}}
                        @php $grand_total+=$approval->other_approval->amount; @endphp
                      </td>
                    </tr>
                    @endif
                    <tr>
                      <td class="text-bold text-danger">
                        TOTAL AMOUNT FOR APPROVAL
                      </td>
                      <td class="text-right text-bold text-danger">
                        {{moneyFormat($grand_total)}}
                      </td>
                    </tr>
                    @if(isset($approval->imprest->id))
                    <tr>
                      <td colspan="2">
                        <legend>Imprest</legend>
                      </td>
                    </tr>
                    <tr>
                      <td >
                        For project: {{implode(",", $approval->imprest->project->pluck('name')->toArray())}}
                      </td>
                      <td class="text-right">
                        Imprest amount: {{moneyFormat($approval->imprest->amount)}}
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2">Remarks: {{$approval->imprest->remarks}}</td>
                    </tr>
                    @endif
                    @if(!in_array($approval->status, ['approved', 'discarded']) && auth()->user()->can('approve-travel'))
                    <tr>
                      <td colspan="2">
                        <form accept="" action="{{ url('travel/') }}" method="post">
                            <table class="table table-bordered">
                              <tr>
                                <td colspan="4">
                                  <legend>Action</legend>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <input required="" type="text" class="form-control" name="remarks" placeholder="Please enter remarks here">
                                </td>
                                <td>
                                  <select required="" class="form-control" name="mark">
                                    <option value="">Select Status</option>
                                    <option value="discussion">Discussion</option>
                                    <option value="discarded">Discard</option>
                                    <option value="approved">Approve</option>
                                  </select>
                                </td>
                                <td>
                                  <input class="btn btn-success" type="submit" name="btn_submit" value="Submit">
                                  {{ csrf_field() }}
                                  <input type="hidden" name="id" value="{{encrypt($approval->id)}}">
                                </td>
                              </tr>
                            </table>
                        </form>
                      </td>
                    </tr>
                    @endif
                    
                    <tr>
                      <td>
                        Status:  
                        @if($approval->status=='hold')
                        <label class="label label-danger">Hold</label>
                        @elseif($approval->status=='discussion')
                        <label class="label label-danger">Discussion</label>
                        @elseif($approval->status=='discarded')
                        <label class="label label-danger">Discarded</label>
                        @elseif($approval->status=='approved')
                        <label class="label label-success">Approved</label>
                        @endif
                      </td>
                      <td>
                        {{$approval->remarks}}
                      </td>
                    </tr>
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
      

  </script>

  @endsection