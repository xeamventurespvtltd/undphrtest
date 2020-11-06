
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
    padding: 80px 15px 0 15px;
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
<body >

     <section class="content-header">
      

      <ol class="breadcrumb">

        <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home></a></li>

        <li><a href="{{ url('salary/view-salary') }}">View Salary</a></li> 

      </ol>

    </section>

    <div class="container">
        <div class="main-content border my-3">

            <div class="upper-content text-center">
                <h1 class="text-uppercase">Xeam Ventures Pvt. Ltd.</h1>
                <p>E-202,Phase 8B, Ind. Area, Mohali (P.B.) -160055</p>
               
            </div>

            <div class="body-content">
                <div class="row m-3">
                    <div class="col-md-12">
                       <h2>You have no salary slip. Please contact Reporting manager.</h2>
                    </div>
                   
    
                    
    
    
                </div>
            </div>

        </div>
    </div>
    </div>
     
</body>
</html>



  
      
 
  
 