<link href="<?php echo base_url('assets/plugins/custombox/css/custombox.css'); ?>" rel="stylesheet">
<script src="<?php echo base_url('assets/plugins/custombox/js/custombox.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/custombox/js/legacy.min.js'); ?>"></script>

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
                    <h4 class="page-title">Agents</h4>
                    <ol class="breadcrumb"> </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card-box table-responsive">
                        <div class="row">
                            <div class="btn-group pull-right">
                                <div class="m-b-10">
                                    <button id="addToTable" class="btn btn-default waves-effect waves-light" onclick="addNew();">Add Agent <i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>

                        <table id="table1" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User ID</th>
                                    <th>Bets</th>
                                    <th>Password</th>
                                    <th>Agent Name</th>
                                    <th>Zone Name</th>
                                    <th>Created At</th>
                                    <th>Odd Options</th>
                                    <th>Actions</th>
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


    <!-- dialog -->

    <div id="eidt-modal" class="modal-demo col-sm-12" style="padding: 0px !important;">
        <button type="button" class="close" onclick="Custombox.close(); tbl.ajax.reload();">
            <span>&times;</span><span class="sr-only">Close</span>
        </button>
        <h4 class="custom-modal-title">Edit Agent</h4>
        <div class="custom-modal-text text-left">
            <div class="profile-detail card-box">
                <form class="form-horizontal" role="form" style="width:480px;">
                    <input type="hidden" id="Id" value="" />
                    <div class="row">
                        <div class="form-group has-success">
                            <label class="col-md-3 control-label">User Id</label>
                            <div class="col-md-3">
                                <input class="form-control" type="text" id="user_id" name="user_id">
                            </div>
                            <label class="col-md-3 control-label">Password</label>
                            <div class="col-md-3">
                                <input class="form-control" type="text" id="password" name="password">
                            </div>
                        </div>

                        <div class="form-group has-success">
                            <label class="col-md-3 control-label">Name</label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="email" name="email">
                            </div>
                        </div>

                        <div class="form-group has-success">
                            <label class="col-md-3 control-label">Zone</label>
                            <div class="col-md-9">
                                <select class="selectpicker" name="zone1" id="zone1" data-style="btn-default btn-custom">
                                    <option value="0" selected></option>
                                    <?php foreach ($zones as $zone) { ?>
                                    <option value="<?php echo $zone->Id; ?>"><?php echo $zone->email; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group has-success">
                            <label class="col-md-3 control-label">Credit Limit</label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="credit_limit" name="credit_limit">
                            </div>
                        </div>

                        <div class="form-group has-success">
                            <label class="col-md-3 control-label">Max Stake</label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="max_stake" name="max_stake">
                            </div>
                        </div>


                        <!-- <div class="form-group has-success">
                        <label class="col-md-3 control-label">Phone</label>
                        <div class="col-md-9">
                            <input class="form-control" type="text" id="phone" name="phone">
                        </div>
                    </div>

                    <div class="form-group has-success">
                        <label class="col-md-3 control-label">Address</label>
                        <div class="col-md-9">
                            <input class="form-control" type="text" id="address" name="address">
                        </div>
                    </div> -->


                        <ul class="list-inline status-list  has-success m-t-20">
                            <li><label class="control-label text-primary">Unders:</label></li>
                            <li>
                                <div class="checkbox checkbox-custom">
                                    <input id="chk_u3" type="checkbox" checked>
                                    <label for="chk_u3">U3</label>
                                </div>
                            </li>
                            <li>
                                <div class="checkbox checkbox-custom">
                                    <input id="chk_u4" type="checkbox" checked>
                                    <label for="chk_u4">U4</label>
                                </div>

                            </li>
                            <li>
                                <div class="checkbox checkbox-custom">
                                    <input id="chk_u5" type="checkbox" checked>
                                    <label for="chk_u5">U5</label>
                                </div>

                            </li>
                            <li>
                                <div class="checkbox checkbox-custom">
                                    <input id="chk_u6" type="checkbox" checked>
                                    <label for="chk_u6">U6</label>
                                </div>
                            </li>
                        </ul>

                        <ul class="list-inline status-list  has-success m-t-20">
                            <li><label class="control-label text-primary">Tags:</label></li>
                            <li>
                                <div class="checkbox checkbox-custom">
                                    <input id="chk_t1" type="checkbox" checked>
                                    <label for="chk_t1">1</label>
                                </div>
                            </li>
                            <li>
                                <div class="checkbox checkbox-custom">
                                    <input id="chk_t2" type="checkbox" checked>
                                    <label for="chk_t2">2</label>
                                </div>

                            </li>
                            <li>
                                <div class="checkbox checkbox-custom">
                                    <input id="chk_t3" type="checkbox" checked>
                                    <label for="chk_t3">3</label>
                                </div>

                            </li>
                            <li>
                                <div class="checkbox checkbox-custom">
                                    <input id="chk_t4" type="checkbox" checked>
                                    <label for="chk_t4">4</label>
                                </div>
                            </li>
                            <li>
                                <div class="checkbox checkbox-custom">
                                    <input id="chk_t5" type="checkbox" checked>
                                    <label for="chk_t5">5</label>
                                </div>
                            </li>
                        </ul>

                        <div>
                            <button type="button" class="btn btn-pink btn-custom btn-rounded waves-effect waves-light" onclick="onSave();">Save</button>
                        </div>

                        <table id="tblOpts" class="table table-striped table-bordered  m-t-10">
                            <thead>
                                <tr>
                                    <th><i class="icon-settings"></i> Option</th>
                                    <th><i class="ion-checkmark-circled"></i> State</th>
                                    <th><i class="ion-ios7-paper-outline"></i> Commission</th>
                                    <th><i class="ion-ios7-paper-outline"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <div class="form-group  m-t-10">
                            <label class="col-md-3 control-label text-primary">Option</label>
                            <div class="col-md-4">
                                <select class="selectpicker" name="Opts" id="Opts" data-style="btn-default btn-custom">
                                    <option value="0" selected></option>
                                    <?php foreach ($options as $opt) { ?>
                                    <option value="<?php echo $opt->Id; ?>"><?php echo $opt->name; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-4 input-group">
                                <span class="input-group-btn">
                                    <input type="number" id="commision" name="commision" class="form-control">
                                    <button type="button" class="btn waves-effect waves-light btn-primary" onclick="saveOption();">Save</button>
                                </span>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <!-- <script src="<?php echo base_url('assets/plugins/switchery/js/switchery.min.js'); ?>"></script> -->
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
                serverSide: true,
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
                ajax: {
                    url: dataUrl,
                    type: "POST",
                },
            });
            return tblObj;
        }

        var tableName = "<?php echo $table; ?>";
        var tbl, tblOpt;

        tbl = initTable("#table1",
            [{
                    targets: [0], //first column 
                    orderable: true, //set not orderable
                    className: "dt-center"
                },
                {
                    targets: [1], //first column 
                    orderable: false, //set not orderable
                    className: "dt-center"
                }
            ], "<?php echo site_url('Cms_api/get_players') ?>"
        );

        var tblOpt = $("#tblOpts").DataTable({
            dom: "lfBrtip",
            buttons: [],
            responsive: !0,
            processing: true,
            serverSide: false,
            "paging": false,
            bFilter: false,
            bInfo: false,
            //Set column definition initialisation properties.
            columnDefs: [{
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
                    targets: [2], //first column 
                    orderable: false, //set not orderable
                    className: "dt-center"
                }
            ],
            ajax: {
                url: "<?php echo site_url('Cms_api/get_player_options/0') ?>",
                type: "POST",
            },
        });

        var $dom = {
            Id: $("#Id"),
            user_id: $("#user_id"),
            password: $("#password"),
            email: $("#email"),
            zone1: $("#zone1"),
            credit_limit: $("#credit_limit"),
            max_stake: $("#max_stake"),
            u3: $("#chk_u3"),
            u4: $("#chk_u4"),
            u5: $("#chk_u5"),
            u6: $("#chk_u6"),
            t1: $("#chk_t1"),
            t2: $("#chk_t2"),
            t3: $("#chk_t3"),
            t4: $("#chk_t4"),
            t5: $("#chk_t5"),
            Opts: $("#Opts"),
            commision: $("#commision"),
        }

        function clearData() {
            $dom.Id.val("");
            $dom.user_id.val("");
            $dom.password.val("");
            $dom.email.val("");
            //$dom.zone1:val("0");
            // $dom.firstname.val("");
            // $dom.lastname.val("");
            // $dom.phone.val("");
            // $dom.address.val("");
            $dom.u3.prop('checked', true);
            $dom.u4.prop('checked', true);
            $dom.u5.prop('checked', true);
            $dom.u6.prop('checked', true);

            $dom.t1.prop('checked', true);
            $dom.t2.prop('checked', true);
            $dom.t3.prop('checked', true);
            $dom.t4.prop('checked', true);
            $dom.t5.prop('checked', true);

            $dom.Opts.val("0");
            $dom.commision.val("");
        }

        function addNew() {
            clearData();
            Custombox.open({
                target: "#eidt-modal",
                effect: "fadein",
                overlaySpeed: "200",
                overlayColor: "#36404a"
            });
        }

        function onEdit(_idx) {
            clearData();
            $.ajax({
                url: "<?php echo site_url('Cms_api/getDataById') ?>",
                data: {
                    Id: _idx,
                    tbl_Name: tableName
                },
                type: "POST",
                dataType: "JSON",
                success: function(data) {
                    $dom.Id.val(data.Id);
                    $dom.user_id.val(data.user_id);
                    $dom.password.val(data.password);
                    $dom.email.val(data.email);

                    $dom.zone1.val(data.agent_id);
                    $dom.zone1.selectpicker('refresh');

                    $dom.credit_limit.val(data.credit_limit);
                    $dom.max_stake.val(data.max_stake);


                    if (data.unders & 1) $dom.u3.prop('checked', true);
                    else $dom.u3.prop('checked', false);
                    if (data.unders & 2) $dom.u4.prop('checked', true);
                    else $dom.u4.prop('checked', false);
                    if (data.unders & 4) $dom.u5.prop('checked', true);
                    else $dom.u5.prop('checked', false);
                    if (data.unders & 8) $dom.u6.prop('checked', true);
                    else $dom.u6.prop('checked', false);

                    if (data.tags & 1) $dom.t1.prop('checked', true);
                    else $dom.t1.prop('checked', false);
                    if (data.tags & 2) $dom.t2.prop('checked', true);
                    else $dom.t2.prop('checked', false);
                    if (data.tags & 4) $dom.t3.prop('checked', true);
                    else $dom.t3.prop('checked', false);
                    if (data.tags & 8) $dom.t4.prop('checked', true);
                    else $dom.t4.prop('checked', false);
                    if (data.tags & 16) $dom.t5.prop('checked', true);
                    else $dom.t5.prop('checked', false);

                    tblOpt.ajax.url("<?php echo site_url('Cms_api/get_player_options') ?>" + "/" + data.Id);
                    tblOpt.ajax.reload();

                    Custombox.open({
                        target: "#eidt-modal",
                        effect: "fadein",
                        overlaySpeed: "200",
                        overlayColor: "#36404a"
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    swal("Error!", "", "error");
                }
            });
        }

        function onDelete(_idx) {
            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this user information!",
                type: "error",
                showCancelButton: true,
                cancelButtonClass: 'btn-white btn-md waves-effect',
                confirmButtonClass: 'btn-danger btn-md waves-effect waves-light',
                confirmButtonText: 'Remove',
                closeOnConfirm: false
            }, function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: "<?php echo site_url('Cms_api/delData') ?>",
                        data: {
                            Id: _idx,
                            tbl_Name: tableName
                        },
                        type: "POST",
                        dataType: "JSON",
                        success: function(data) {
                            swal("Remove!", "", "success");
                            tbl.ajax.reload();
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            swal("Error!", "", "error");
                        }
                    });
                }
            });
        }

        function onSave() {
            Custombox.close();

            var unders = 0;
            if (document.getElementById("chk_u3").checked) unders += 1;
            if (document.getElementById("chk_u4").checked) unders += 2;
            if (document.getElementById("chk_u5").checked) unders += 4;
            if (document.getElementById("chk_u6").checked) unders += 8;

            if (unders == 0) {
                swal("Error!", "Please select at least one 'Under'.", "error");
                return;
            }

            var tags = 0;
            if (document.getElementById("chk_t1").checked) tags += 1;
            if (document.getElementById("chk_t2").checked) tags += 2;
            if (document.getElementById("chk_t3").checked) tags += 4;
            if (document.getElementById("chk_t4").checked) tags += 8;
            if (document.getElementById("chk_t5").checked) tags += 16;

            if (tags == 0) {
                swal("Error!", "Please select at least one 'Under'.", "error");
                return;
            }

            $.ajax({
                url: "<?php echo site_url('Cms_api/edit_player') ?>",
                data: {
                    Id: $dom.Id.val(),
                    user_id: $dom.user_id.val(),
                    password: $dom.password.val(),
                    email: $dom.email.val(),
                    agent_id: $dom.zone1.val(),
                    credit_limit: $dom.credit_limit.val(),
                    max_stake: $dom.max_stake.val(),
                    unders: unders,
                    tags: tags,
                },
                type: "POST",
                dataType: "JSON",
                success: function(data) {
                    tbl.ajax.reload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    swal("Error!", "", "error");
                }
            });
        }

        function saveOption() {
            if ($dom.Id.val() == "") return;
            var opId = $dom.Opts.val();
            if (opId == "0") return;
            var commision = $dom.commision.val();

            $.ajax({
                url: "<?php echo site_url('Cms_api/edit_player_option') ?>",
                data: {
                    user_id: $dom.Id.val(),
                    option_id: opId,
                    commision: commision
                },
                type: "POST",
                dataType: "JSON",
                success: function(data) {
                    tblOpt.ajax.reload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    swal("Error!", "", "error");
                }
            });
        }

        function onEnableOption(id, status) {
            $.ajax({
                url: "<?php echo site_url('Cms_api/enable_player_option') ?>",
                data: {
                    Id: id,
                    status: status
                },
                type: "POST",
                dataType: "JSON",
                success: function(data) {
                    tblOpt.ajax.reload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    swal("Error!", "", "error");
                }
            });
        }

    </script>