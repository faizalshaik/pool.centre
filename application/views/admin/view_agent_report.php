<link href="<?php echo base_url('assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css'); ?>" rel="stylesheet">
<link href="<?php echo base_url('assets/plugins/bootstrap-daterangepicker/daterangepicker.css'); ?>" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>

<!-- ============================================================== -->
<!-- Start right Content here -->
<!-- ============================================================== -->
<div class="content-page">
    <!-- Start content -->
    <div class="content">
        <div class="container">
            <!-- Page-Title -->
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="page-title">Agent Report</h4>
                    <ol class="breadcrumb"> </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="panel panel-color panel-primary">
                        <div class="panel-heading">
                            <div class="btn-group pull-right">
                                <button type="button" class="btn btn-danger btn-custom btn-rounded waves-effect waves-light" onclick="onSearch();">Search</button>
                            </div>
                            <h3 class="panel-title">Agent Report</h3>
                        </div>
                        <div class="panel-body" style="background-color:lightblue;">
                            <form class="form-horizontal" role="form">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group has-success">
                                            <label class="col-md-6 control-label">Week</label>
                                            <div class="col-md-6">
                                                <select class="selectpicker" name="week" id="week" data-style="btn-default btn-custom">
                                                    <?php foreach ($weeks as $week) { ?>
                                                        <option value="<?php echo $week->week_no; ?>" <?php if ($curWeekNo == $week->week_no) echo 'selected'; ?>><?php echo $week->week_no; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-4">
                                        <div class="form-group has-success">
                                            <label class="col-md-6 control-label">Zone</label>
                                            <div class="col-md-6">
                                                <select class="selectpicker" name="zone" id="zone" data-style="btn-default btn-custom">
                                                    <option value="0" selected>ALL</option>
                                                    <?php foreach ($agents as $user) { ?>
                                                        <option value="<?php echo $user->Id; ?>"><?php echo $user->email; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="btn-group pull-right">
                                            <button type="button" class="btn btn-primary btn-custom btn-rounded waves-effect waves-light" onclick="printHtml();">Print</button>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card-box table-responsive">
                        <table id="table1" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Week</th>
                                    <th>User Id</th>
                                    <th>Agent Name</th>
                                    <th>Zone</th>
                                    <th>Sales</th>
                                    <th>Win</th>
                                    <th>Terminal</th>
                                    <th>Tags</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div> <!-- container -->
    </div> <!-- content -->

    <script type="text/javascript">
        function initTable(tagId, cols, dataUrl) {
            var tblObj = $(tagId).DataTable({
                dom: "lfBrtip",
                buttons: [{
                    extend: "copy",
                    className: "btn-sm"
                }, {
                    extend: "csv",
                    className: "btn-sm"
                }, {
                    extend: "excel",
                    className: "btn-sm"
                }, {
                    extend: "pdf",
                    className: "btn-sm"
                }, {
                    extend: "print",
                    className: "btn-sm"
                }],
                responsive: !0,
                processing: true,
                serverSide: false,
                sPaginationType: "full_numbers",
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-right"></i>',
                        previous: '<i class="fa fa-angle-left"></i>',
                        first: '<i class="fa fa-angle-double-left"></i>',
                        last: '<i class="fa fa-angle-double-right"></i>'
                    }
                },
                //Set column definition initialisation properties.
                columnDefs: cols,
                aLengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                iDisplayLength: -1,
                ajax: {
                    url: dataUrl,
                    type: "POST",
                },
            });
            return tblObj;
        }
        var tableName = "<?php echo $table; ?>";
        var tblSummary;

        tblSummary = initTable("#table1",
            [{
                    targets: [0], //first column 
                    orderable: true, //set not orderable
                    className: "dt-center"
                },
                {
                    targets: [1], //first column 
                    orderable: false, //set not orderable
                    className: "dt-center"
                },
                {
                    targets: [-1], //last column
                    orderable: false, //set not orderable
                    className: "actions dt-center"
                }
            ], "<?php echo site_url('Cms_api/get_agent_report') . '/' . $curWeekNo; ?>");


        function onSearch() {
            week = document.getElementById('week').value;
            zone = document.getElementById('zone').value;
            //terminal = document.getElementById('terminal').value;

            let params = '/' + week + '/' + zone;
            tblSummary.ajax.url("<?php echo site_url('Cms_api/get_agent_report') ?>" + params);
            tblSummary.ajax.reload();
        }


        //print part
        var PAGE_WIDTH = 500;
        var PAGE_HEIGHT = 700;
        const content = [];
        function getPngDimensions(base64) {
            const header = atob(base64.slice(22, 70)).slice(16, 24);
            const uint8 = Uint8Array.from(header, c => c.charCodeAt(0));
            const dataView = new DataView(uint8.buffer);
            return {
                width: dataView.getInt32(0),
                height: dataView.getInt32(4)
            };
        }
        const splitImage = (img, content, callback) => () => {
            var canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const printHeight = img.height * PAGE_WIDTH / img.width;

            canvas.width = PAGE_WIDTH;

            for (let pages = 0; printHeight > pages * PAGE_HEIGHT; pages++) {
                /* Don't use full height for the last image */
                canvas.height = Math.min(PAGE_HEIGHT, printHeight - pages * PAGE_HEIGHT);
                ctx.drawImage(img, 0, -pages * PAGE_HEIGHT, canvas.width, printHeight);
                content.push({
                    image: canvas.toDataURL(),
                    margin: [0, 5],
                    width: PAGE_WIDTH
                });
            }
            callback();
        };

        function next() {
            /* add other content here, can call addImage() again for example */
            pdfMake.createPdf({
                content
            }).download();
        }

        function printHtml() {
            var ele = document.getElementById('table1');
            html2canvas(ele, {
                onrendered: function(canvas) {
                    var image = canvas.toDataURL();

                    // var tmpLink = document.createElement( 'a' );  
                    // tmpLink.download = 'image.png'; 
                    // // set the name of the download file 
                    // tmpLink.href = image;    
                    // // temporarily add link to body and initiate the download  
                    // document.body.appendChild( tmpLink );  
                    // tmpLink.click();  
                    // document.body.removeChild( tmpLink );

                    const {
                        width,
                        height
                    } = getPngDimensions(image);

                    const printHeight = height * PAGE_WIDTH / width;
                    if (printHeight > PAGE_HEIGHT) {
                        const img = new Image();
                        img.onload = splitImage(img, content, next);
                        img.src = image;
                        return;
                    }

                    content.push({
                        image,
                        margin: [0, 5],
                        width: PAGE_WIDTH
                    });
                    next();
                }
            });
        }
    </script>