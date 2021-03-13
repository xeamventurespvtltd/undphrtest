@extends('admins.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Upload Leave Pool
            <!-- <small>Control panel</small> -->
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ url('leaves/leave-report-form') }}">Back</a></li>
        </ol>
    </section>
    @include('admins.validation_errors')
    <!-- Main content -->
    <section class="content">
        <!-- Small boxes (Stat box) -->
        <div class="row">
            <div class="box">
                <form method="post" action="{{ url('upload') }}" enctype="multipart/form-data">
                    @csrf
                    <br>
                    <!-- <div class="col-md-12">   -->
                    <div class="row">    
                        <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">  
                            <div class="form-group">
                                <label>Year<sup class="ast">*</sup></label>
                                <select class="form-control input-sm basic-detail-input-style" id="year" name="year">
                                    <option value="" disabled>Please select Year</option>
                                    @for($year = date("Y"); $year >=2020; $year--)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">     
                            <div class="form-group">
                                <label>Month<sup class="ast">*</sup></label>
                                <select class="form-control input-sm basic-detail-input-style" id="month" name="month">
                                    <option value="" disabled>Please select Month</option>
                                    <option value="1">Dec-Jan</option>
                                    <option value="2">Jan-Feb</option>
                                    <option value="3">Feb-Mar</option>
                                    <option value="4">Mar-Apr</option>
                                    <option value="5">Apr-May</option>
                                    <option value="6">May-Jun</option>
                                    <option value="7">Jun-Jul</option>
                                    <option value="8">Jul-Aug</option>
                                    <option value="9">Aug-Sep</option>
                                    <option value="10">Sep-Oct</option>
                                    <option value="11">Oct-Nov</option>
                                    <option value="12">Nov-Dec</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">                          
                            <div class="form-group">
                            <input type="file" name="leave_detail">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-md-6 col-sm-6 col-xs-12">
                            <input type="submit">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection