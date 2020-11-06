<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cost Estimation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 3.3.7 -->
  <link href="{{asset('public/admin_assets/bower_components/bootstrap/dist/css/bootstrap.min.css')}}" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="{{asset('public/admin_assets/bower_components/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
  <!--my main css-->
  <link href="{!! asset('public/admin_assets/plugins/jquery-toast/jquery.toast.min.css') !!}" rel="stylesheet">
  <link href="{!! asset('public/admin_assets/dist/css/cost_estimation_sheet.css') !!}" rel="stylesheet">
  <!-- <link rel="stylesheet" href="css/mystyle.css"> -->
  <!--google fonts link-->
  <link href="https://fonts.googleapis.com/css?family=Be+Vietnam&display=swap" rel="stylesheet">
  <!--jquery script-->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>
<body>
<!--Main outer container starts here-->
<div class="wrapper">
  <h1>Cost Estimation Sheet</h1>
  <hr>
  @include('admins.validation_errors')

  <h2 class="sub-heading">Project Financial Scope</h2>
  <!-- /.box-header -->
  <form id="cost_form" action="javascript:void(0)" method="POST" class="form-horizontal">

    @php 
      $costEstimation   = $tilDraft->costEstimationDraft->estimation_data;
      $estimationData   = json_decode($costEstimation);
      $noOfDesignations = count($estimationData->project_scope->resource_name);
      $isComplete       = $tilDraft->costEstimationDraft->is_complete ?? 0;
    @endphp

    <input type="hidden" name="til_draft_id" value="{!! $tilDraft->id !!}">
    <input type="hidden" name="is_complete" value="{!! $isComplete !!}">
    <!--Project Financial Scope Starts Here-->
    <section class="section_project_scope">
      <div class="flex-container flex_container_to_append">
        <div class="flex-item">
            <h3 class="main-flex-heading">Heads</h3>
            <span class="strip-bg cell-padding">Number of Resources</span>
            <span class="cell-padding">Min. wages</span>
            <span class="strip-bg cell-padding">Allowances EPF</span>
            <span class="cell-padding">EPF Wages/ month</span>
            <span class="strip-bg cell-padding">Non EPF Allownaces/ month</span>
            <span class="cell-padding">Gross</span>
            <span class="strip-bg cell-padding">EPF @13%</span>
            <span class="cell-padding">ESIC @ 3.25% if gross 21k</span>
            <span class="strip-bg cell-padding">Bonus @ 8.33%</span>
            <span class="cell-padding">Others</span>
            <span class="strip-bg cell-padding">CTC</span>
            <span class="cell-padding">Total</span>
        </div>
        <div class="flex-item">
            <h3 class="main-flex-heading">Formula</h3>
            <span class="strip-bg formula-text">A</span>
            <span class=" formula-text">B</span>
            <span class="strip-bg formula-text">C</span>
            <span class="formula-text">D = B + C</span>
            <span class="strip-bg formula-text">E</span>
            <span class="formula-text">F = D + E</span>
            <span class="strip-bg formula-text">G = D * 13%</span>
            <span class="formula-text">H = F * 3.25%</span>
            <span class="strip-bg formula-text">I = B * 8.33% or 7000 * 8.33% (if B < 7000)</span>
            <span class="formula-text">J = Label + value in rs.</span>
            <span class="strip-bg formula-text">K = F + G + H + I + J</span>
            <span class="formula-text">L = A * K</span>
        </div>
        
        @if(!empty($noOfDesignations) && $noOfDesignations > 0)
          @for($i = 0; $i < $noOfDesignations; $i++)
            <div class="flex-item item-container @if($i == 0)itemToBeCloned @endif">
              <div class="input-heading-box">
                @php
                  $resourceName = $estimationData->project_scope->resource_name[$i] ?? null;
                @endphp
                
                <input type="text" name="project_scope[resource_name][]" class="input-heading r-inputs" placeholder="Resource" value="{!! $resourceName !!}" readonly>
              </div>
              <div class="input-strip">
                @php
                  $noOfResources = $estimationData->project_scope->no_of_resources[$i] ?? 0;
                @endphp
                
                <input type="number" name="project_scope[no_of_resources][]" class="no-of-resources cell-padding r-inputs" onkeyup="calculateSep1($(this))" value="{!! numberFormat($noOfResources) !!}" readonly>
              </div>
              <div>
                @php
                  $minWages = $estimationData->project_scope->min_wages[$i] ?? 0;
                @endphp
                
                <input type="number" name="project_scope[min_wages][]" id="" class="min-wages cell-padding r-inputs" onkeyup="calculateSep1($(this))" value="{!! numberFormat($minWages) !!}" readonly>
              </div>
              <div class="input-strip">
                @php
                  $allowanceEpf = $estimationData->project_scope->allowance_epf[$i] ?? 0;
                @endphp
                
                <input type="number" name="project_scope[allowance_epf][]" id="" class="epf r-inputs" onkeyup="calculateSep1($(this))" value="{!! numberFormat($allowanceEpf) !!}" readonly>
              </div>
              <div>
                @php
                  $epfWagesPerMonth = $estimationData->project_scope->epf_wages_per_month[$i] ?? 0;
                @endphp
                
                <input type="number" name="project_scope[epf_wages_per_month][]" class="epf-wages cell-padding r-inputs" onkeyup="calculateSep1($(this))" value="{!! numberFormat($epfWagesPerMonth) !!}" readonly>
              </div>
              <div class="input-strip">
                @php
                  $nonEpfAllowance = $estimationData->project_scope->non_epf_allowance[$i] ?? 0;
                @endphp
                
                <input type="number" name="project_scope[non_epf_allowance][]" class="non-epf r-inputs" onkeyup="calculateSep1($(this))" value="{!! numberFormat($nonEpfAllowance) !!}" readonly>
              </div>
              <div>
                @php
                  $gross = $estimationData->project_scope->gross[$i] ?? 0;
                @endphp
                
                <input type="number" name="project_scope[gross][]" class="gross cell-padding r-inputs" onkeyup="calculateSep1($(this), 'gross')" value="{!! numberFormat($gross) !!}" readonly>
              </div>
              <div class="input-strip">
                @php
                  $epf13 = $estimationData->project_scope->epf_13[$i] ?? 0;
                @endphp
                
                <input type="number" name="project_scope[epf_13][]" class="epf-13 r-inputs" onkeyup="calculateSep1($(this), 'epf')" value="{!! numberFormat($epf13) !!}" readonly>
              </div>
              <div>
                @php
                  $esic325 = $estimationData->project_scope->esic_325[$i] ?? 0;
                @endphp

                <input type="number" name="project_scope[esic_325][]" class="esic cell-padding r-inputs" onkeyup="calculateSep1($(this), 'esic')" value="{!! numberFormat($esic325) !!}" readonly>
              </div>
              <div class="input-strip">
                @php
                  $bonus833 = $estimationData->project_scope->bonus_833[$i] ?? 0;
                @endphp

                <input type="number" name="project_scope[bonus_833][]" class="bonus833 r-inputs" onkeyup="calculateSep1($(this), 'bonus833')" value="{!! numberFormat($bonus833) !!}" readonly>
              </div>
              <div>
                @php
                  $others = $estimationData->project_scope->others[$i] ?? 0;
                @endphp

                <input type="number" name="project_scope[others][]" class="otherss cell-padding r-inputs" onkeyup="calculateSep1($(this), 'others')" value="{!! numberFormat($others) !!}" readonly>
              </div>
              <div class="input-strip">
                @php
                  $ctc = $estimationData->project_scope->ctc[$i] ?? 0;
                @endphp

                <input type="number" name="project_scope[ctc][]" id="" class="ctc r-inputs" onkeyup="calculateSep1($(this), 'ctc')" value="{!! numberFormat($ctc) !!}" readonly>
              </div>
              <div>
                @php
                  $total = $estimationData->project_scope->total[$i] ?? 0;
                @endphp

                <input type="number" name="project_scope[total][]" class="total-1 cell-padding r-inputs" onkeyup="calculateSep1($(this), 'total')" value="{!! numberFormat($total) !!}" readonly>
              </div>
            </div>
          @endfor
        @endif
      </div>
    </section>
    <!--Project Financial Scope Ends Here-->
    <!--Results Section Starts Here-->
    <section class="section_results">
      <div class="flex-container">
        <div class="flex-item">
            <h3 class="main-flex-heading">Results</h3>
            <span class="strip-bg cell-padding">Total number of HRs</span>
            <span class="cell-padding">Monthly turnover</span>
            <span class="strip-bg cell-padding">Anuual Gross Wages (Tenure = 12 Months)</span>
            <span class="cell-padding">Tenure in Months</span>
            <span class="strip-bg cell-padding">Tenure Gross Wages (Tenure = 24 Months)</span>
        </div>
        <div class="flex-item">
            <h3 class="main-flex-heading">Formula</h3>
            <span class="strip-bg formula-text">M=A1+A2+A3+A4+....</span>
            <span class=" formula-text">N=L1+L2+L3+L4+....</span>
            <span class="strip-bg formula-text">O=N*12</span>
            <span class="formula-text">P</span>
            <span class="strip-bg formula-text">Q=N*P</span>
        </div>
        <div class="flex-item">
          @php
            $costEstimation = $estimationData->cost_estimation;
          @endphp
        </div>
        <div class="flex-item">
          <h3 class="main-flex-heading">Values</h3>
          <div class="input-strip">
            @php
              $totalNumberOfHr = $costEstimation->total_number_of_hr ?? 0;
            @endphp

            <input type="number" name="cost_estimation[total_number_of_hr]" id="total-number-of-resources" class="total-no-of-resources cell-padding r-inputs" onkeyup="calculateSep1($(this))" value="{!! numberFormat($totalNumberOfHr) !!}" readonly>
          </div>
          <div>
            @php
              $monthlyTurnover = $costEstimation->monthly_turnover ?? 0;
            @endphp

            <input type="number" name="cost_estimation[monthly_turnover]" class="monthly_turnover cell-padding r-inputs" onkeyup="calculateSep1($(this))" value="{!! numberFormat($monthlyTurnover) !!}" readonly>
          </div>
          <div class="input-strip">
            @php
              $annualGross = $costEstimation->annual_gross ?? 0;
            @endphp

            <input type="number" name="cost_estimation[annual_gross]" id="annual-gross" class="r-inputs" onkeyup="calculateSep1($(this))" value="{!! numberFormat($annualGross) !!}" readonly>
          </div>
          <div>
            @php
              $tenureInMonth = $costEstimation->tenure_in_month ?? 0;
            @endphp

            <input type="number" name="cost_estimation[tenure_in_month]" id="tenure-in-month" class="cell-padding r-inputs" onkeyup="calculateSep1($(this))" value="{!! numberFormat($tenureInMonth) !!}" readonly>
          </div>
          <div>
            @php
              $tenureGrossWages = $costEstimation->tenure_gross_wages ?? 0;
            @endphp

            <input type="number" name="cost_estimation[tenure_gross_wages]" id="tenure-gross-wages" class="cell-padding r-inputs" onkeyup="calculateSep1($(this))" value="{!! numberFormat($tenureGrossWages) !!}" readonly>
          </div>
        </div>
      </div>
    </section>
    <!--Results Section Ends Here-->
    @php
      $costFactor = $estimationData->cost_factors;
      $details    = $estimationData->cost_factor_details;
    @endphp
    <!--Cost Factors Starts Here-->
    <hr>
    <section>
      <h2 class="sub-heading">Cost Factors</h2>
      <div style="padding: 10px;">
        <table>
          <thead>
            <tr>
              <th>Select</th>
              <th>Costs</th>
              <th>Value(R)</th>
              <th>Multiplier(S)</th>
              <th>Total(R*S)</th>
              <th>MI / Tenure</th>
              <th>CI per Unit</th>
              <th>Cap / Op</th>
              <th>Attach Excel</th>
              <th>Comments</th>
            </tr>
          </thead>
          <tbody class="cost-factor-tbody">
            @if(!empty($costFactorOptions))
              @php
                $sr = 0; $assetPath = asset('public');
                $costFactorFile = (array) @$details->cost_factor_file;
                $fileAbsolutePath = \Config::get('constants.uploadPaths.tilDocument');
                $filePath = $assetPath.\Config::get('constants.uploadPaths.tilDocumentPath');
              @endphp

              @foreach($costFactorOptions as $key => $option)
                @if(isset($details->option_id[$sr]) && $details->option_id[$sr] == $key) 
                  
                  <tr class="factors-tr">
                    <td style="text-align: center;">
                      <input type="checkbox" name="cost_factor_details[record][]" class="cost-factor-check cost_option_id" value="{!! $key !!}" checked>
                    </td>
                    <td>
                      @if($key != 30)
                        <span class="formula-text cf-long-text">{!! $option !!}</span>
                      @else
                        <input type="text" name="cost_factor_details[operational_costs][]" class="td-inputs r-inputs" value="{!! $option !!}" readonly>
                      @endif
                    </td>
                    <td>
                      @php
                        $costFactorValue = 0;
                        if(isset($details->cost_factor_value[$sr]) && !empty($details->cost_factor_value[$sr])) {
                          $costFactorValue = $details->cost_factor_value[$sr];
                        }
                      @endphp
                      <input type="number" name="cost_factor_details[cost_factor_value][]" class="cf-value td-inputsss r-inputs" onkeyup="costFactorCal($(this))" value="{!! numberFormat($costFactorValue) !!}" readonly>
                    </td>
                    <td>
                      @php
                        $costFactorMultiplier = 0;
                        if(isset($details->cost_factor_multiplier[$sr]) && !empty($details->cost_factor_multiplier[$sr])) {
                          $costFactorMultiplier = $details->cost_factor_multiplier[$sr];
                        }
                      @endphp

                      <input type="number" name="cost_factor_details[cost_factor_multiplier][]" class="cf-multiplier td-inputsss r-inputs" onkeyup="costFactorCal($(this))" value="{!! numberFormat($costFactorMultiplier) !!}" readonly>
                    </td>
                    <td>
                      @php
                        $costFactorTotal = 0;
                        if(isset($details->cost_factor_total[$sr]) && !empty($details->cost_factor_total[$sr])) {
                          $costFactorTotal = $details->cost_factor_total[$sr];
                        }
                      @endphp

                      <input type="number" name="cost_factor_details[cost_factor_total][]" class="cf-row-total td-inputsss total_input r-inputs" value="{!! numberFormat($costFactorTotal) !!}" readonly>
                    </td>
                    <td>
                      @php
                        $monthlyImpactByTenure = 0;
                        if(isset($details->monthly_impact_by_tenure[$sr]) && !empty($details->monthly_impact_by_tenure[$sr])) {
                          $monthlyImpactByTenure = $details->monthly_impact_by_tenure[$sr];
                        }
                      @endphp

                      <input type="number" name="cost_factor_details[monthly_impact_by_tenure][]" class="cf-row-total td-inputsss mi-by-t r-inputs" value="{!! numberFormat($monthlyImpactByTenure) !!}" readonly>
                    </td>
                    <td>
                      @php
                        $costImpactPerUnit = 0;
                        if(isset($details->cost_impact_per_unit[$sr]) && !empty($details->cost_impact_per_unit[$sr])) {
                          $costImpactPerUnit = $details->cost_impact_per_unit[$sr];
                        }
                      @endphp

                      <input type="number" name="cost_factor_details[cost_impact_per_unit][]" class="cf-row-total td-inputsss ci-per-unit r-inputs" value="{!! numberFormat($costImpactPerUnit) !!}" readonly>
                    </td>
                    <td>
                        @foreach($costFactorTypeOptions as $typeIdkey => $costFactorType)
                          @if(isset($details->capital_operational_expense[$sr]) && $details->capital_operational_expense[$sr] == $typeIdkey)
                            <input type="text" name="cost_factor_details[capital_operational_expense][]" class="cf-row-total td-inputsss r-inputs" value="{!! $costFactorType !!}" readonly>
                          @endif
                        @endforeach
                    </td>
                    <td>
                      @if(isset($costFactorFile[$sr]) && !empty($costFactorFile[$sr]) && file_exists($fileAbsolutePath . $costFactorFile[$sr]))
                        <a href="{!! $filePath . $costFactorFile[$sr] !!}" target="_blank">
                          Attachment
                        </a>
                      @else
                        --
                      @endif
                    </td>
                    <td>
                      @php
                        $costFactorComment = null;
                        if(isset($details->cost_factor_comment[$sr]) && !empty($details->cost_factor_comment[$sr])) {
                          $costFactorComment = $details->cost_factor_comment[$sr];
                        }
                      @endphp
                      <textarea name="cost_factor_details[cost_factor_comment][]" id="" cols="15" rows="2" class="cf-comments cost_inputs" readonly>{!! $costFactorComment !!}</textarea>
                    </td>
                  </tr>
                @endif

                @php $sr++; @endphp
              @endforeach
            @endif
          </tbody>
          <tfoot>
            <tr>
              <th colspan="4" style="text-align: center">Total Amount</th>
              <th style="padding: 0px;">
                @php
                  $totalCostFactorAmount = $costFactor->total_cost_factor_amount ?? 0; 
                @endphp
                <input type="number" name="cost_factors[total_cost_factor_amount]" id="total-sum-amount" class="cf-row-total td-inputsss r-inputs subTotal1" value="{!! numberFormat($totalCostFactorAmount) !!}" min="0" readonly>
              </th>
              <th style="padding: 0px;">
                @php
                  $totalCostFactorMonthlyTenure = $costFactor->total_cost_factor_monthly_tenure ?? 0; 
                @endphp
                <input type="number" name="cost_factors[total_cost_factor_monthly_tenure]" id="total-mi-tenure" class="cf-row-total td-inputsss r-inputs subTotal1" value="{!! numberFormat($totalCostFactorMonthlyTenure) !!}" min="0" readonly>
              </th>
              <th style="padding: 0px;">
                @php
                  $totalCostImpactPerUnit = $costFactor->total_cost_impact_per_unit ?? 0; 
                @endphp

                <input type="number" name="cost_factors[total_cost_impact_per_unit]" id="total-ci-per-unit" class="cf-row-total td-inputsss r-inputs subTotal1" value="{!! numberFormat($totalCostImpactPerUnit) !!}" min="0" readonly>
              </th>
              <th colspan="3"></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </section>
    <!--Cost Factors Ends Here-->
    <!--Revenue Starts Here-->
    <hr>
    @php
      $revenue = $estimationData->revenue;
    @endphp
    <section>
      <h2 class="sub-heading">Revenue</h2>
      <div class="flex-container col-revenue">
        <div class="flex-item">
          <h3 class="main-flex-heading">Revenue Heads</h3>
          <span class="strip-bg cell-padding">Service Charge</span>
          <span class="cell-padding">Recruitment fee</span>
          <span class="strip-bg cell-padding">Income from Refundable Security deposit</span>
          <span class="cell-padding">Application fees</span>
          <span class="strip-bg cell-padding">Other</span>
          <span class="cell-padding">Total</span>
        </div>
        <div class="flex-item">
          <h3 class="main-flex-heading">Rate (% / Value)(R)</h3>
          <div class="input-strip text-center">
            @php
              $serviceChargeRate = $revenue->service_charge_rate ?? 0;
            @endphp

            <input type="number" name="revenue[service_charge_rate]" class="cell-padding rev1 rev-rate r-inputs" onkeyup="revenue($(this))" value="{!! numberFormat($serviceChargeRate) !!}" readonly>
          </div>
          <div class="text-center">
            @php
              $recruitmentFeeRate = $revenue->recruitment_fee_rate ?? 0;
            @endphp

            <input type="number" name="revenue[recruitment_fee_rate]" class="cell-padding rev2 rev-rate r-inputs" onkeyup="revenue($(this))" value="{!! numberFormat($recruitmentFeeRate) !!}" readonly>
          </div>
          <div class="input-strip text-center">
            @php
              $refundableSecurityDepositRate = $revenue->refundable_security_deposit_rate ?? 0;
            @endphp

            <input type="number" name="revenue[refundable_security_deposit_rate]" class="rev3 rev-rate r-inputs" onkeyup="revenue($(this))" value="{!! numberFormat($refundableSecurityDepositRate) !!}" readonly>
          </div>
          <div class="text-center">
            @php
              $applicationFeesRate = $revenue->application_fees_rate ?? 0;
            @endphp

            <input type="number" name="revenue[application_fees_rate]" class="cell-padding rev4 rev-rate r-inputs" onkeyup="revenue($(this))" value="{!! numberFormat($applicationFeesRate) !!}" readonly>
          </div>
          <div class="input-strip text-center">
            @php
              $otherRevenueRate = $revenue->other_revenue_rate ?? 0;
            @endphp

            <input type="number" name="revenue[other_revenue_rate]" class="cell-padding rev5 rev-rate r-inputs" onkeyup="revenue($(this))" value="{!! numberFormat($otherRevenueRate) !!}" readonly>
          </div>
          <div class="text-center">
            @php
              $totalRevenueRate = $revenue->total_revenue_rate ?? 0;
            @endphp

            <input type="number" name="revenue[total_revenue_rate]" class="cell-padding r-inputs rev6 rev-rate-total r-inputs" onkeyup="revenue($(this))" value="{!! numberFormat($totalRevenueRate) !!}" readonly>
          </div>
        </div>
        <div class="flex-item">
          <h3 class="main-flex-heading">Multiplier(S)</h3>
          <div class="input-strip">
            @php
              $serviceChargeMultiplier = $revenue->service_charge_multiplier ?? 0;
            @endphp

            <input type="number" name="revenue[service_charge_multiplier]" class="cell-padding rev7 rev-multiplier r-inputs" onkeyup="revenue($(this))" value="{!! numberFormat($serviceChargeMultiplier) !!}" readonly>
          </div>
          <div>
            @php
              $recruitmentFeeMultiplier = $revenue->recruitment_fee_multiplier ?? 0;
            @endphp

            <input type="number" name="revenue[recruitment_fee_multiplier]" class="cell-padding rev8 rev-multiplier r-inputs" onkeyup="revenue($(this))" value="{!! numberFormat($recruitmentFeeMultiplier) !!}" readonly>
          </div>
          <div class="input-strip">
            @php
              $refundableSecurityDepositMultiplier = $revenue->refundable_security_deposit_multiplier ?? 0;
            @endphp

            <input type="number" name="revenue[refundable_security_deposit_multiplier]" class="rev9 rev-multiplier r-inputs" onkeyup="revenue($(this))" value="{!! numberFormat($refundableSecurityDepositMultiplier) !!}" readonly>
          </div>
          <div>
            @php
              $applicationFeesMultiplier = $revenue->application_fees_multiplier ?? 0;
            @endphp

            <input type="number" name="revenue[application_fees_multiplier]" class="cell-padding rev10 rev-multiplier r-inputs" onkeyup="revenue($(this))" value="{!! numberFormat($applicationFeesMultiplier) !!}" readonly>
          </div>
          <div>
            @php
              $otherRevenueMultiplier = $revenue->other_revenue_multiplier ?? 0;
            @endphp

            <input type="number" name="revenue[other_revenue_multiplier]" class="cell-padding rev11 rev-multiplier r-inputs" onkeyup="revenue($(this))" value="{!! numberFormat($otherRevenueMultiplier) !!}" readonly>
          </div>
          <div>
            @php
              $totalRevenueMultiplier = $revenue->total_revenue_multiplier ?? 0;
            @endphp

            <input type="number" name="revenue[total_revenue_multiplier]" class="cell-padding r-inputs rev12 rev-multiplier-total r-inputs" onkeyup="revenue($(this))" value="{!! numberFormat($totalRevenueMultiplier) !!}" readonly>
          </div>
        </div>
        <div class="flex-item">
          <h3 class="main-flex-heading">Monthly Revenue(R*S)</h3>
          <div class="input-strip text-center">
            @php
              $serviceChargeMonthlyRevenue = $revenue->service_charge_monthly_revenue ?? 0;
            @endphp

            <input type="number" name="revenue[service_charge_monthly_revenue]" class="cell-padding r-inputs rev13 monthly-revenue" onkeyup="revenue($(this))" value="{!! numberFormat($serviceChargeMonthlyRevenue) !!}" readonly>
          </div>
          <div class="text-center">
            @php
              $recruitmentFeeMonthlyRevenue = $revenue->recruitment_fee_monthly_revenue ?? 0;
            @endphp

            <input type="number" name="revenue[recruitment_fee_monthly_revenue]" id="" class="cell-padding r-inputs rev14 monthly-revenue" onkeyup="revenue($(this))" value="{!! numberFormat($recruitmentFeeMonthlyRevenue) !!}" readonly>
          </div>
          <div class="input-strip text-center">
            @php
              $refundableSecurityDepositMonthlyRevenue = $revenue->refundable_security_deposit_monthly_revenue ?? 0;
            @endphp

            <input type="number" name="revenue[refundable_security_deposit_monthly_revenue]" id="annual-gross" class="r-inputs rev15 monthly-revenue" onkeyup="revenue($(this))" value="{!! numberFormat($refundableSecurityDepositMonthlyRevenue) !!}" readonly>
          </div>
          <div class="text-center">
            @php
              $applicationFeesMonthlyRevenue = $revenue->application_fees_monthly_revenue ?? 0;
            @endphp

            <input type="number" name="revenue[application_fees_monthly_revenue]" id="" class="cell-padding r-inputs rev16 monthly-revenue" onkeyup="revenue($(this))" value="{!! numberFormat($applicationFeesMonthlyRevenue) !!}" readonly>
          </div>
          <div class="input-strip text-center">
            @php
              $otherMonthlyRevenue = $revenue->other_monthly_revenue ?? 0;
            @endphp

            <input type="number" name="revenue[other_monthly_revenue]" id="" class="cell-padding r-inputs rev17 monthly-revenue" onkeyup="revenue($(this))" value="{!! numberFormat($otherMonthlyRevenue) !!}" readonly>
          </div>
          <div class="text-center">
            @php
              $totalMonthlyRevenue = $revenue->total_monthly_revenue ?? 0;
            @endphp

            <input type="number" name="revenue[total_monthly_revenue]" id="" class="cell-padding r-inputs rev18 total-monthly-revenue" onkeyup="revenue($(this))" value="{!! numberFormat($totalMonthlyRevenue) !!}" readonly>
          </div>
        </div>
      </div>
    </section>
    <!--Revenue Ends Here-->
    <!--Capital Expenses Starts Here-->
    <hr>
    @php
      $totalCapitalExpense = $estimationData->total_capital_expense ?? 0;
      $totalOperationalExpense = $estimationData->total_operational_expense ?? 0;
    @endphp
    <section>
      <!-- <h2 class="sub-heading">Capital Expenses</h2> -->
      <div class="col-md-6">
        <div class="form-group">
          <h2 class="sub-heading">Capital Expenses</h2>
          <div class="flex-container">
            <div class="flex-item"> 
              <h3 class="main-flex-heading">Total Capital Expenses</h3>
            </div>
            <div class="flex-item">
              <div class="text-center">
                <input type="number" name="total_capital_expense" id="total_capital_expense" class="input-heading cell-padding r-inputs" value="{!! numberFormat($totalCapitalExpense) !!}" readonly>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-group">
          <h2 class="sub-heading">Operational Investment</h2>
          <div class="flex-container">
            <div class="flex-item"> 
              <h3 class="main-flex-heading">Total Operational Investment</h3>
            </div>
            <div class="flex-item">
              <div class="input-strip text-center">
                <input type="number" name="total_operational_expense" id="total_operational_expense" class="input-heading cell-padding r-inputs" value="{!! numberFormat($totalOperationalExpense) !!}" readonly>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="clearfix"></div>
    </section>
    <!--Capital Expenses Ends Here-->
    <!--Operational Investment Starts Here-->
    <hr>
    @php
      $totalExpense = $estimationData->total_expense ?? 0;
    @endphp
    <section>
      <h2 class="sub-heading">Total Expense</h2>
      <div class="flex-container">
        <div class="flex-item"> 
          <h3 class="main-flex-heading">(Q + Capital Expenses + Operational Investment)</h3>
        </div>
        <div class="flex-item">
          <div class="text-center">
            <input type="number" name="total_expense" id="total_expense" class="input-heading cell-padding r-inputs" value="{!! numberFormat($totalExpense) !!}" readonly>
          </div>
        </div>
      </div>
    </section>
    <!--Operational Investment Ends Here-->
  </form>
</div>
<!--Main outer container ends here-->
</body>
</html>