@extends('admins.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Export Leave Detail
                <!-- <small>Control panel</small> -->
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ url('leaves/leave-report-form') }}">Back</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="box">
                    <form method="post" action="{{ url('export') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="leave_detail">
                        <input type="submit">
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
