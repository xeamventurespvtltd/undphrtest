<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Salary Slip</title>

<link rel="stylesheet" href="{{asset('public/admin_assets/dist/css/bootstrap.min.css')}}">
<link rel="stylesheet" href="{{asset('public/admin_assets/dist/css/style.css')}}">
<style>.content-header > .breadcrumb > li > a {
    color: #444;
    text-decoration: none;
    display: inline-block;
}
.content-header > .breadcrumb {
    float: right;
    background: transparent;
    margin-top: 0;
    margin-bottom: 0;
    font-size: 12px;
    padding: 7px 5px;
    position: absolute;
    top: 15px;
    right: 10px;
    border-radius: 2px;
}
.breadcrumb > li {
    display: inline-block;
}
.breadcrumb > li a:hover {
    color:#657881;
}
.content-header {
    position: relative;
    padding: 60px 15px 0 15px;
}
.breadcrumb {
    padding: 8px 15px;
    margin-bottom: 20px;
    list-style: none;
    background-color: #f5f5f5;
    border-radius: 4px;
}
</style>

</head>
<body onload="window.print()">
     <section class="content-header">


      <ol class="breadcrumb">

        <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home></a></li>

        <li><a href="{{ url('salary/view-salary') }}">View Salary</a></li>

      </ol>

    </section>



    @php

     /*   $basic_amount_cal = @$data['salary_detail']->basic_amount / 30 * @$data['salary_detail']->pay_days;

        $basic_ta_amount = @$data['salary_detail']->ta_amount / 30 * @$data['salary_detail']->pay_days;

        $total_basic_amount = $basic_amount_cal + $basic_ta_amount;
*/
    @endphp

    <div class="container">
        <div class="main-content border my-3">

            <div class="upper-content text-center">
                <h1 class="text-uppercase">Xeam Ventures Pvt. Ltd.</h1>
                <p>E-202,Phase 8B, Ind. Area, Mohali (P.B.) -160055</p>
                <h2>Salary Slip From

                @php

                   @$sal_month = $data['salary_detail']->salary_month;
                    @$sal_year = $data['salary_detail']->salary_year;
                    if($sal_month==1){
                        $prev_month = 12;
                        $prev_year = $sal_year-1;
                    }else{
                        $prev_month = $sal_month-1;
                         $prev_year =  $sal_year;
                    }

                $start_date =  date('d-m-Y', strtotime("26-".$prev_month."-".$prev_year));
                    $end_date =  date('d-m-Y', strtotime("25-".$sal_month."-".$sal_year));
                    @endphp
                    <span class="text-primary "> {{$start_date}}</span> to
                    <span class="text-primary"> {{$end_date}}</span>
                </h2>
            </div>

            <div class="body-content">
                <div class="row m-3">
                    <div class="col-md-6">
                        <table class="table table-striped table-bordered">
                            <tr>
                                <th>Employee Name:</th>
                                <td>{{@$data['salary_detail']->emp_name}}</td>
                            </tr>
                            <tr>
                                <th>Emp Code</th>
                                <td>{{@$data['salary_detail']->emp_code}}</td>
                            </tr>
                            <tr>
                                <th>Father Name</th>
                                <td>{{@$data['emp_detail']->father_name}}</td>
                            </tr>
                            <tr>

                                <th>Designation</th>
                                <td>{{@$data['designation_detail']->designation[0]->name}}</td>
                            </tr>
                            <tr>
                                <th>Department</th>
                                <td>eVIN UNDP</td>
                            </tr>
                            <tr>
                                <th>Present Days</th>
                                <td>{{@$data['salary_detail']->present_days}}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <table class="table table-striped table-bordered">
                            <tr>
                                <th>Bank A/c No.</th>
                                <td>{{@$data['emp_acc_detail']->bank_account_number}}</td>
                            </tr>

                            <tr>
                                <th>PF. No.</th>
                                <td>{{@$data['salary_detail']->pf_no}}</td>
                            </tr>

                            <tr>
                                <th>Pay Days</th>
                                <td>{{@$data['salary_detail']->pay_days}}</td>
                            </tr>
                            <tr>
                                @php

                                if($data['salary_detail']->esi_no==""){
                                    $esi = "N/A";
                                }else{
                                    $esi = $data['salary_detail']->esi_no;
                                }

                                @endphp
                                <th>ESI No.</th>
                                <td>{{$esi}}</td>
                            </tr>
                            <!-- <tr>
                                <th>Mode of Pay</th>
                                <td>{{@$data['salary_detail']->pay_mode}}</td>
                            </tr> -->
                            <tr>
                                @php
                                if($data['salary_detail']->uan==""){
                                    $uan = "N/A";
                                }else{
                                    $uan = $data['salary_detail']->uan;
                                }
                                @endphp

                                <th>UAN</th>
                                <td>{{$uan}}</td>
                            </tr>
                            <tr>
                                 <th>IFSC Code</th>
                                <td>{{@$data['emp_acc_detail']->ifsc_code}}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-12">
                        <table class="table table-striped table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th>Earnings</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                    <th>Deductions</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>BASIC<br/><br/>TA<br/><br/><br/><br/><br/><br/></td>
                                    <td>{{@$data['salary_detail']->basic_rate}}<br/><br/>{{@$data['salary_detail']->ta_rate}}<br/><br/><br/><br/><br/><br/></td>

                                     <td>{{@$data['salary_detail']->basic_amount}}<hr/>{{@$data['salary_detail']->ta_amount}}<br/><br/><br/><br/><br/><br/></td>
                                    <td>OTHER DED<hr/><br/><br/><br/><br/><br/><br/></td>
                                    <td>{{@$data['salary_detail']->deduction_amount1}}<br/>{{@$data['salary_detail']->deduction_amount2}}</td>
                                </tr>

                                <tr>
                                    <td><b>Total</b></td>
                                    <td><b>{{@$data['total_rate']}}</b></td>
                                    <td><b>{{@$data['total_amount']}}</b></td>
                                    <td><b>Total</b></td>
                                    <td><b>@if(@$data['total_deduction']!=0) {{@$data['total_deduction']}} @else - @endif</b></td>
                                </tr>

                                <tr>
                                    <th>Net Pay</th>
                                    <th colspan="4">{{@$data['salary_detail']->net_pay}}</th>
                                </tr>
                                <tr>
                                    <th>In Words</th>
                                    <th colspan="4">{{@$data['num_in_words']}}</th>
                                </tr>
                            </tbody>
                        </table>


                    </div>

                    <div class="col-md-12 mt-5">
                        <h3>Please note this is computer generated report.</h3>
                        <p><b>For EPF:</b> If you are covered under EPFO scheme you can check the EPF contributions. Open Google and
                            type Download UAN passbook click on below link <a href="#"> https://passbook.epfindia.gov.in</a> Member Passbook
                            Login with your UAN No and Password.
                        </p>
                        <p><b>For ESIC:</b> If you are covered under ESIC scheme you can check the ESIC contributions. Open
                            <a href="#">www.esic.in</a>
                            and click on IP portal than enter your ESIC no and Captcha for login then click on contribution
                            detail.
                            <a href="#">www.xeamventures.com</a>
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>
    </div>
     <script src="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
  <script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
  <script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>

    <script src="{{asset('public/admin_assets/dist/js/jquery.min.js')}}"></script>
  <script src="{{asset('public/admin_assets/dist/js/bootstrap.min.js')}}"></script>
</body>
</html>







