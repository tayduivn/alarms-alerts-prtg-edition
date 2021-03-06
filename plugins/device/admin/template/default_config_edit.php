<?php include_once APP_PATH.'admin/template/header.php';?>

<?php if ($page2 == "e") { ?>
<div class="alert alert-danger fade in">
  <button type="button" class="close" data-dismiss="alert">×</button>
  <?php echo $tl["errorpage"]["sql"];?>
</div>
<?php } ?>

<?php if ($errors) { ?>
<div class="alert alert-danger fade in">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <?php if (isset($errors["e"])) echo $errors["e"];
          if (isset($errors["e1"])) echo $errors["e1"];
          if (isset($errors["e2"])) echo $errors["e2"];
          if (isset($errors["e3"])) echo $errors["e3"];
    ?>
</div>
<?php } ?>

<p>
    <a href="index.php?p=device&amp;sp=default_config" class="btn btn-success"> Back to default Config</a>
</p>
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Device default configuration</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                                <input type="hidden" name="id" value="<?php if (isset($JAK_FORM_DATA["id"])) echo $JAK_FORM_DATA["id"];?>">
                                <div class="form-group<?php if (isset($errors["e1"])) echo " has-error";?>">
                                    <label>Device Type</label>
                                    <input type="text" class="form-control" value="<?php if (isset($JAK_FORM_DATA["device_type"])) echo $JAK_FORM_DATA["device_type"];?>" readonly="readonly" name="device_type">
                                    
                                </div>
                                <div class="form-group<?php if (isset($errors["e2"])) echo " has-error";?>">
                                    <label>Config data</label>
                                    <textarea required="required" class="form-control" rows="12" cols="20" name="default_config_data" id="default_config_data" >
                                        <?php if (isset($JAK_FORM_DATA["default_config_data"])) echo $JAK_FORM_DATA["default_config_data"];?>
                                        
                                    </textarea>
                                </div>
                                <div class="form-group<?php if (isset($errors["e3"])) echo " has-error";?>">
                                    <label>File Type</label>
                                    <select class="form-control" required="required" name="config_file_type">
                                        <option value="">Select config file type</option>
                                        <option value="json" <?php if ($JAK_FORM_DATA['config_file_type'] == 'json') echo ' selected="selected"' ; ?> >json</option>
                                        <option value="dat" <?php if ($JAK_FORM_DATA['config_file_type'] == 'dat') echo ' selected="selected"' ; ?>>dat</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-default">Submit</button>
                            </form>
                    </div>
                    <div class="box-footer"></div>
                </div>
            </div>
        </div>

<script type="text/javascript">
    $(document).ready(function() {    });
</script>

<?php include_once APP_PATH.'admin/template/footer.php';?>
