<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('bootstrap.min.css') }}" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
@if(count($data) <= 0)
    <script>
        alert("No report of this transaction");
        window.location.href = "{{ route('admin.monthlyreport') }}";
    </script>
@endif
<style>
    @media print {
        body {
            visibility: hidden;
        }
        #printarea {
            visibility: visible;
        }
        @page { size: landscape }
    }
    #dep {  
        width: 150px;
        height: 150px;   
        float: left; 
    } 
    #shs {  
        width: 150px;
        height: 150px;   
        float: right; 
    } 
    table, thead, tr, th{
        border: 1px solid black;
    }
    table{
        border-top: double;
        border-bottom: double;
        width: 100%;
        font-size: 18px;
    }
    td{
        word-wrap: break-word;
    }
</style>
<body>
    <div id="printarea">
        <div class="row">
            <div class="col-sm-2">
                <img id="dep" src="{{ asset('admintemplate/assets/img/shs-logo.png') }}" alt="">
            </div>
            <div class="col-sm-10">
                <center style="font-family: Tahoma; font-weight: bold;">
                    {{ $category[0]->category }} REPORT OF PROPERTIES<br>
                    LUGAIT SENIOR HIGH SCHOOL<br>
                    DISTRICT OF LUGAIT
                    <br>
                    <?php
                    function ordinal($number) {
                        $suffix = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
                        if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
                            $abbreviation = $number . 'th';
                        } else {
                            $abbreviation = $number . $suffix[$number % 10];
                        }
                        return $abbreviation;
                    }
                    ?>
                    <p style="color: skyblue; font-weight: 900; font-size: 30px">
                        @if(is_numeric($month))
                            <?php 
                                $dateObj = DateTime::createFromFormat('!m', $month);
                                $monthName = $dateObj->format('F'); 
                                $monthName = strtoupper($monthName);
                            ?>
                            @if(isset($week_number) && is_numeric($week_number))
                                <?php $ordinalWeek = ordinal($week_number); ?>
                                {{ $ordinalWeek }} WEEK REPORT FOR THE MONTH OF {{$monthName}} {{ $year }}
                            @else
                                MONTHLY REPORT FOR {{$monthName}} {{ $year }}
                            @endif
                        @endif
                        @if($month == "N")
                            FOR THE YEAR OF {{ $year }}
                        @endif
                        @if($month[0] == "Q")
                            <?php
                            $month = ltrim($month, 'Q');
                            $ordinalMonth = ordinal($month);
                            ?>
                            FOR THE {{ $ordinalMonth }} QUARTER OF THE YEAR {{ $year }}
                        @endif
                        @if($month[0] !== "Q" && $month !== "N" && !is_numeric($month))
                            FOR THE MONTH OF {{ $month }} {{ $year }}
                        @endif
                    </p>

                </center>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <table class="table" rules="all">
                    <thead style="text-transform: uppercase">
                        <tr>
                            <th>ARTICLE</th>
                            <th>DESCRIPTION (SPECIFICATIONS/SERIAL NUMBER AND ETC)</th>
                            <th>DATE OF ACQUISITION</th>
                            <th>QUANTITY</th>
                            <th>UNIT OF MEASURE</th>
                            <th>UNIT VALUE</th>
                            <th>TOTAL COST/VALUE</th>
                            <th>SOURCE OF FUND (MOOE OR SEF/LGU)</th>
                            <th>TYPE OF SEMI-EXPANDABLE PROPERTIES</th>
                            <th>FUND CLUSTER (UACS CODE)</th>
                            <th>NAME OF SCHOOL</th>
                            <th>NAME OF ACCOUNTABLE OFFICER</th>
                            <th>WITH IAR, ICS AND DR? (Y/N)</th>
                            <th>REMARKS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total_cost = 0; ?>
                        @if(count($data) > 0)
                            <?php $count = 1; ?>
                            @foreach($data as $d)
                                <tr>
                                    <td style="text-align: center"></td>
                                    <td>{{ $d->item }}</td>
                                    <td>{{ $d->dateRequest }}</td>
                                    <td style="text-align: center">{{ $d->totalReleased }}</td>
                                    <td style="text-align: center">{{ $d->unit }}</td>
                                    <td style="text-align: right">&#8369;&nbsp;{{ number_format((float)$d->cost, 2, '.', ',') }}</td>
                                    <td style="text-align: right">&#8369;&nbsp;{{ number_format((float)$d->cost * $d->totalReleased, 2, '.', ',') }}</td>
                                    <td style="text-align: center"></td>
                                    <td style="text-align: center">{{ $d->category }}</td>
                                    <td style="text-align: center"></td>
                                    <td>{{ $d->department_name }}</td>
                                    <td style="text-align: center">{{ $d->fullname }}</td>
                                    <td style="text-align: center"></td>
                                    <td style="text-align: center">{{ $d->remarks }}</td>
                                </tr>
                                <?php $total_cost += $d->cost * $d->totalReleased; $count++; ?>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr align="center">
                            <td colspan="7">Total Cost &#8369;&nbsp; {{ number_format((float)$total_cost, 2, '.', ',') }}</td>
                            <td colspan="7">{{ count($data) }} Items in Total</td>
                        </tr>
                    </tfoot>
                </table>
                <br><br>
                @if(count($data) > 0)
                <div class="row">
                    <div class="col-md-6">
                        Prepared By: <br><br>
                        {{ Auth::user()->fullname }} <br>
                        School Property Custodian <br>
                    </div>
                    <div class="col-md-6">
                        Noted By: <br><br>
                        ELMA WOLFRAN, PhD.  <br>
                        Head Teacher IV <br>
                    </div>
                </div><br>
                @endif
            </div>
        </div>
    </div>
</body>
</html>

<script>
    window.onload = function () {
        window.print();
    }
</script>
