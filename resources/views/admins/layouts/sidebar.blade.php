<?php
$usser = Auth::user();
$designation_login_data = DB::table('designation_user as du')

    ->where('du.user_id','=',Auth::id())

    ->select('du.id', 'du.user_id','du.designation_id')->first();

$designation_login_user = $designation_login_data->designation_id;

$userBasic = [
    'pic' => config('constants.static.profilePic'),
    "fullName" => ""
];

if(!empty($usser)) {
    $user = getEmployeeProfileData($usser->id);

    if(!empty($user)) {
        $userBasic['fullName'] = $user->fullname;

        if(!empty($user->profile_picture)) {
            $userBasic['pic'] = config('constants.uploadPaths.profilePic').$user->profile_picture;
        }
    }
}
?>
<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{@$userBasic['pic']}}" class="img-circle" alt="User Image">
            </div>

            <div class="pull-left info">
                <p>{{@$userBasic['fullName']}}</p>
                <a href="javascript:void(0)"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">MAIN NAVIGATION</li>
            <li>
                <a href="{{ url('employees/dashboard') }}">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>
            @if($designation_login_user!=6)
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-users"></i> <span>Employees Management</span>
                        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
                    </a>
                    <ul class="treeview-menu">
                        @can('create-employee')
                            <li class=""><a href="{{ url('employees/create') }}"><i class="fa fa-circle-o text-red"></i>New Registration</a></li>
                        @endcan
                        <li class=""><a href="{{ url('employees/list') }}"><i class="fa fa-circle-o text-aqua"></i>Employees List</a></li>

                        @can('replace-authority')
                            <li class=""><a href="{{ url('employees/replace-authority') }}"><i class="fa fa-circle-o text-yellow"></i>Replace Authority</a></li>
                        @endcan
                    </ul>
                </li>
            @endif

            @if($designation_login_user!=6)

                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-plane fa-lg"></i> <span>Leaves Management</span>
                        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class=""><a title="Apply for leave" href="{{ url('leaves/apply-leave') }}"><i class="fa fa-circle-o text-red"></i>Apply for leave</a></li>
                        <li class=""><a title="List of applied leave" href="{{ url('leaves/applied-leaves') }}"><i class="fa fa-circle-o text-aqua"></i>Applied Leaves</a></li>

                        @if($designation_login_user!=4)
                            <li class=""><a title="list of Approval Employee's leave" href="{{ url('leaves/approve-leaves') }}"><i class="fa fa-circle-o text-success"></i>Approve Leaves</a></li>
                            <li class=""><a title="list of Employee's leave Pool" href="{{ url('leaves/leave-pools') }}"><i class="fa fa-circle-o text-success"></i>Leaves Pool</a></li>

                    @endif
                    <!-- <li class=""><a title="View leave report" href="{{ url('leaves/leave-report-form') }}"><i class="fa fa-circle-o text-red"></i>Leave Report</a></li>-->

                    <!-- <li class=""><a href="{{ url('leaves/policies') }}"><i class="fa fa-circle-o text-warning"></i>Leave Policies</a></li> -->
                        <!-- <li class=""><a href="javascript:void(0)"><i class="fa fa-circle-o text-primary"></i>Leave Allotment</a></li> -->
                    <!--<li class=""><a href="{{ url('leaves/holidays') }}"><i class="fa fa-circle-o text-secondary"></i>Holidays List</a></li>-->
                    </ul>
                </li>
            @endif

            @if($designation_login_user!=6)


                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-calendar"></i> <span>Attendance Management</span>
                        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class=""><a title="View your own attendance" href="{{ url('attendances/my-attendance') }}"><i class="fa fa-circle-o text-red"></i>My Attendance </a></li>


                        <!--can('view-attendance') -->
                        @if($designation_login_user!=4)
                            <li class=""><a title="View department wise attendance" href="{{ url('attendances/consolidated-attendance-sheets') }}"><i class="fa fa-circle-o text-aqua"></i>Attendance Sheets</a></li>
                        @endif
                    <!--<li class=""><a title="List of attendances to be verified by you" href="{{ url('attendances/verify-attendance-list') }}"><i class="fa fa-circle-o text-purple"></i>Verify Attendance</a></li>-->
                        <!--endcan -->
                        @if(auth()->user()->can('change-attendance') || auth()->user()->can('it-attendance-approver'))
                            <li class=""><a  title="Approve change attendance requests" href="{{ url('attendances/change-approvals') }}"><i class="fa fa-circle-o text-orange"></i>Change Approvals</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            <li class="treeview">
                <a href="#">
                    <i class="fa fa-money"></i> <span>Salary Management</span>
                    <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
                </a>
                <ul class="treeview-menu">
                    @if(Auth::user()->employee_code == 'XEAM001')
                        <li class=""><a title="Upload salary slip" href="{{ url('salary/upload-salary-slip') }}"><i class="fa fa-circle-o text-red"></i>Upload salary slip</a></li>
                    @endif

                    <li class=""><a title="View your salary slip" href="{{ url('salary/view-salary') }}"><i class="fa fa-circle-o text-red"></i>Salary slip</a></li>

                </ul>
            </li>

            @if($designation_login_user!=6)

                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-plus-square"></i> <span>Insurance Policy</span>
                        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
                    </a>
                    <ul class="treeview-menu">

                        <li class=""><a title="Download mediclaim policy" href="{{ asset('public/uploads/documents/BHARTI').'/BHARTI  Justin Rema addition Bharti.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>Mediclaim(3L) 1</a></li>
                        <li class=""><a title="Download mediclaim policy" href="{{ asset('public/uploads/documents/BHARTI').'/BHARTI 00014_EN_00_20200929142559 Additions 10.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>Mediclaim(3L) 2</a></li>
                        <li class=""><a title="Download mediclaim policy" href="{{ asset('public/uploads/documents/BHARTI').'/BHARTI Gavi.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>Mediclaim(3L) 3</a></li>
                        <li class=""><a title="Download mediclaim policy" href="{{ asset('public/uploads/documents/BHARTI').'/Bharti AXA EN_00_20201020171203 additons 7.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>Mediclaim(3L) 4</a></li>
                        <li class=""><a title="Download mediclaim policy" href="{{ asset('public/uploads/documents/BHARTI').'/BHARTI BHUVNESHWAR SHARMA Bharti AXA.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>Mediclaim(3L) 5</a></li>
                        <li class=""><a title="Download mediclaim policy" href="{{ asset('public/uploads/documents/BHARTI').'/Bharti GMC addition.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>Mediclaim(3L) 6</a></li>
                        <li class=""><a title="Download mediclaim policy" href="{{ asset('public/uploads/documents/BHARTI').'/BHARTI_00015_EN_00_20201005194510 Additions 21.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>Mediclaim(3L) 7</a></li>
                        <li class=""><a title="Download mediclaim policy" href="{{ asset('public/uploads/documents/BHARTI').'/BHARTI_AXA_Group_Mediclaim_Policy.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>Mediclaim(3L) 8</a></li>
                        <li class=""><a title="Download mediclaim policy" href="{{ asset('public/uploads/documents/BHARTI').'/Xeam Bharti Addition-01-02-2021.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>Mediclaim(3L) 9</a></li>

                        <li class=""><a title="Download life policy" href="{{ asset('public/uploads/documents/ICICI').'/ICICI _ONMember_AnnuextureAfter_Accounting.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>ICICI Life insurance(10L) 1</a></li>
                        <li class=""><a title="Download life policy" href="{{ asset('public/uploads/documents/ICICI').'/ICICI GTC addition1.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>ICICI Life insurance(10L) 2</a></li>
                        <li class=""><a title="Download life policy" href="{{ asset('public/uploads/documents/ICICI').'/ICICI GTC addition2.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>ICICI Life insurance(10L) 3</a></li>
                        <li class=""><a title="Download life policy" href="{{ asset('public/uploads/documents/ICICI').'/ICICI POLICY.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>ICICI Life insurance(10L) 4</a></li>
                        <li class=""><a title="Download life policy" href="{{ asset('public/uploads/documents/ICICI').'/ICICI_ONMember_AnnuextureAfter_Accounting.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>ICICI Life insurance(10L) 5</a></li>
                        <li class=""><a title="Download life policy" href="{{ asset('public/uploads/documents/ICICI').'/ICICI_Life_Insurance_Policy.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>ICICI Life insurance(10L) 6</a></li>
                        <li class=""><a title="Download life policy" href="{{ asset('public/uploads/documents/ICICI').'/ICICI Prudentails _Additions-01-02-2021.pdf' }}"  target=”_blank”><i class="fa fa-circle-o text-red"></i>ICICI Life insurance(10L) 7</a></li>



                    </ul>
                </li>
            @endif

        </ul>
    </section>
    <!-- /.sidebar -->
</aside>

<script type="text/javascript">
    /** add active class and stay opened when selected */
    var url = window.location;
    // for sidebar menu entirely but not cover treeview
    $('ul.sidebar-menu a').filter(function() {
        return this.href == url;
    }).parent().addClass('active');
    // for treeview
    $('ul.treeview-menu a').filter(function() {
        return this.href == url;
    }).parentsUntil(".sidebar-menu > .treeview-menu").addClass('active');
</script>
