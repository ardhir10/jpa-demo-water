@extends('layouts.main')
@section('page_title',$page_title)
@section('css')
<style>
    .extra-bold {
        text-shadow: 0px 1px, 1px 0px, 1px 1px;
        letter-spacing: 1px;
    }

    .dashboard-header {
        border-top-right-radius: 50px;
        border-bottom-right-radius: 50px;
        background: #00597A;
    }

    .rounded-top-20 {
        border-top-left-radius: 20px !important;
        border-top-right-radius: 20px !important;
    }

    .table-responsive::-webkit-scrollbar {
        -webkit-appearance: none;
    }

    .table-responsive::-webkit-scrollbar:vertical {
        width: 12px;
    }

    .table-responsive::-webkit-scrollbar:horizontal {
        height: 12px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, .5);
        border-radius: 10px;
        border: 2px solid #ffffff;
    }

    .table-responsive::-webkit-scrollbar-track {
        border-radius: 10px;
        background-color: #ffffff;
    }

</style>
@endsection
@section('content')
<div class="br-mainpanel">

    <div class="br-pagebody">
        <div class=" text-white rounded-20 pd-t-20 mg-t-50 mg-b-30">
            <div class="d-flex  bg-royal rounded-20 pd-10 text-white wd-300 animated fadeInLeft"
                style="margin-top: -40px;    box-shadow: -2px 13px 16px 0px rgba(0, 0, 0, 0.21);">
                <img src="{{asset('backend/images/dashboard/monitoring.png')}}" class="ht-50 rounded-circle" alt="">
                <h4 class="mg-b-0 mg-t-10 mg-l-10 " style="   letter-spacing: 1px;">{{$page_title}}</h4>
            </div>
            <div class="row row-sm">
            </div>
        </div>

        <div class=" text-white">

            <div class="row row-sm mg-b-30 ">
                <div class="col-lg-8" style="z-index:99">
                    <div class="card  rounded-20  tx-black shadow animated fadeInUp">
                        <div class="card-header  rounded-top-20  tx-medium bd-0 stx-18">
                            <i class="ion icon ion-calendar"></i> Filter Report
                        </div>
                        <div class="card-body ">
                            <div class="row row-sm">
                                <div class="col-xl-4">
                                    <div class="form-group ">
                                        <label> period :</label>
                                        <select class="form-control select2" data-placeholder="Choose one"
                                            id="daterange">
                                            <option value="day">Daily</option>
                                            <option value="month">Monthly</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-xl-4" style="z-index: 9;">
                                    <div class="form-group  " id="datepicker-date-area">
                                        <label> Date :</label>
                                        <input type="text" name="date" id="date" value="{{date('Y-m-d')}}"
                                            autocomplete="off" class="datepicker form-control time" required>
                                    </div>
                                    <div class="form-group hilang" id="datepicker-month-area">
                                        <label> Month :</label>
                                        <input type="text" name="date" id="month" value="{{date('Y-m')}}"
                                            autocomplete="off" class="datepicker-month form-control   time" required>
                                    </div>
                                </div>
                                <div class="col-xl-2">
                                    <div class="form-group">
                                        <button class="btn btn-magenta btn-block mg-t-30"
                                            onclick="showChart()">SUBMIT</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-sm">


                <div class="col-lg-12">
                    @foreach ($sensors as $sensor)
                    <div class="card royal text-white rounded-20 pd-20 mg-t-40 shadow animated fadeIn">
                        <div class="text-center d-flex  bg-grandeur rounded-20 pd-10 text-white shadow"
                            style="width:fit-content;margin-top: -40px;    box-shadow: -2px 13px 16px 0px rgba(0, 0, 0, 0.21);">
                            <img src="{{asset('backend/images/icon/water.png')}}" class="ht-40 rounded-circle" alt="">
                            <span class="tx-bold mg-b-0 mg-t-10 mg-l-5 "
                                style="text-shadow: -3px 2px 9px #0000;letter-spacing: 1px;">{{$sensor->sensor_display}}
                            </span>
                        </div>
                        {{-- <p class="text-right hidden-sm-down" style="margin-top: -40px;">Tuesday ,21 April 2020</p> --}}


                        <div class="bg-royal pd-10 rounded-20 wd-100p mg-t-20 ht-500" id="{{$sensor->sensor_name}}"
                            width="">
                        </div>

                    </div>
                    @endforeach


                </div>
            </div>
        </div>
    </div>
</div>
</div><!-- br-pagebody -->
@push('js')
<script src="{{asset('backend/js/reports/trends.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>

<script>
    showChart();

    function showChart() {
        let daterange = $('#daterange').val();
        if (daterange == 'day' || daterange == 'minute') {
            date = $('#date').val()
        } else if (daterange == 'month') {
            date = $('#month').val()
        } else if (daterange == 'year') {
            date = $('#year').val()
        }
        axios.get(`{{url('/')}}` + '/api/sensors')
            .then(async function (response) {
                let sensors = response.data;
                let allDataChart = await dataChart(daterange, date);
                console.log(allDataChart);
                for (const key in sensors) {
                    let sensor = sensors[key];
                    if ($('#' + sensor.sensor_name).length) {
                        chart(sensor.sensor_name, sensor.sensor_display, allDataChart.global.tstamp,  allDataChart.sensors[sensor.sensor_name]);
                        resizeChart(sensor.sensor_name);
                    }
                }
                Swal.fire(
                    'Success',
                    'Data Loaded Successfully  !',
                    'success'
                ).then((result) => {
                    // location.reload();
                })
            })
            .catch(function (error) {
                Swal.fire(
                    'Failde',
                    'Fail Load data  !',
                    'warning'
                ).then((result) => {
                    // location.reload();
                })
            });
    }


    async function dataChart(daterange, date) {
        const data = await axios.post(`{{url('/')}}` + '/api/trending', {
            daterange: daterange,
            date: date
        });
        return data.data;
         
    }


    var table = $('.datatable').DataTable();
    $('.datepicker').datepicker({
        format: "yyyy-mm-dd",
        startView: 2,
        minViewMode: 0,
        language: "id",
        daysOfWeekHighlighted: "0",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        container: '#datepicker-date-area'
    });

    $('.datepicker-month').datepicker({
        format: "yyyy-mm",
        startView: 2,
        minViewMode: 1,
        language: "id",
        daysOfWeekHighlighted: "0",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        container: '#datepicker-month-area'
    });

    $('.datepicker-year').datepicker({
        format: "yyyy",
        startView: 2,
        minViewMode: 2,
        language: "id",
        daysOfWeekHighlighted: "0",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        container: '.datepicker-area'
    });

    $('#daterange').on('change', function () {
        val = $(this).val();
        if (val == 'day') {
            $('#datepicker-date-area').removeClass('hilang ');
            const element = document.querySelector('#datepicker-date-area')
            element.classList.add('animated', 'fadeIn')
            // Hilangkan Month
            $('#datepicker-month-area').addClass('hilang ');

        } else {
            $('#datepicker-month-area').removeClass('hilang');
            const element = document.querySelector('#datepicker-month-area')
            element.classList.add('animated', 'fadeIn')
            // Hilangkan Date
            $('#datepicker-date-area').addClass('hilang ');

        }
    })

</script>
@endpush
@include('layouts.partials.footer')
</div><!-- br-mainpanel -->
@endsection
