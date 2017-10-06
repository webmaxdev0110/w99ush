<?php

/*MAIN SCRIPT*/
global $wpdb;

$sql = "select * from xiblox_destination_info where id = 1";
$result = $wpdb->get_results( $sql, ARRAY_A );

$destination_url = $result[0]["destination_url"];
$db_host = $result[0]["db_host"];
$db_name = $result[0]["db_name"];
$db_user = $result[0]["db_user"];
$db_password = $result[0]["db_password"];
$db_prefix = $result[0]["db_prefix"];
$destination_path = $result[0]["destination_path"];

if (empty($destination_url) || empty($db_host) || empty($db_user) || empty($db_name)){
	echo "<div class=\"updated fade\">";
	echo "Please configure this plugin before using it. <a href=\"admin.php?page=XIBLOX/xi_settings.php\">Configure</a>";
	echo "</div>";
	exit();
}
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>XIPUSH Publish</h2>
    </div>
    <div class="col-lg-2">
        <?php echo get_site_url(); ?>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-md-10">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    Resources
                </div>
                <div class="ibox-content">
                    <input type="hidden" id="selected_post" class="selected-hidden" />
                    <input type="hidden" id="selected_page" class="selected-hidden" />
                    <input type="hidden" id="selected_media" class="selected-hidden" />
                    <input type="hidden" id="selected_theme" class="selected-hidden" />
                    <input type="hidden" id="selected_plugin" class="selected-hidden" />
                    <input type="hidden" id="selected_user" class="selected-hidden" />
                    <input type="hidden" id="selected_custom_post" class="selected-hidden" />
                    <input type="hidden" id="selected_tables" class="selected-hidden" />
                    <input type="hidden" id="selected_menu" class="selected-hidden" />
                    <input type="hidden" id="selected_blox" class="selected-hidden" />

                    <div class="row">
                        <div class="col-md-2">
                            <input type="hidden" id="plugin_path" value = "<?php echo plugins_url().'/XIBLOX'; ?>" />
                            <button name="submit" class="btn btn-primary" id = "pub" >Push</button>
                            <button name="submit" class="btn btn-info" data-toggle="modal" data-target="#dialog">Push Log</button>
                        </div>
                        <div class="col-md-10">
                            <button type="button" class="btn btn-danger m-r-sm no-selected" id="no_post">0</button><strong>Posts</strong>&nbsp;&nbsp;
                            <button type="button" class="btn btn-primary m-r-sm no-selected" id="no_page">0</button><strong>Pages</strong>&nbsp;&nbsp;
                            <button type="button" class="btn btn-info m-r-sm no-selected" id="no_media">0</button><strong>Media</strong>&nbsp;&nbsp;
                            <button type="button" class="btn btn-success m-r-sm no-selected" id="no_theme">0</button><strong>Themes</strong>&nbsp;&nbsp;
                            <button type="button" class="btn btn-warning m-r-sm no-selected" id="no_plugin">0</button><strong>Plugins</strong>&nbsp;&nbsp;
                            <button type="button" class="btn btn-danger m-r-sm no-selected" id="no_user">0</button><strong>Users</strong>&nbsp;&nbsp;
                            <button type="button" class="btn btn-primary m-r-sm no-selected" id="no_custom_post">0</button><strong>Custom Post</strong>&nbsp;&nbsp;
                            <button type="button" class="btn btn-success m-r-sm no-selected" id="no_menu">0</button><strong>Menu</strong>&nbsp;&nbsp;
                        </div>
                    </div>
                    <div class="p-xs">
                        <div id="xiprogress" class="push-progress progress progress-medium">
                            <div class="progress-bar progress-bar-info progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                0%
                            </div>
                    </div>
                       
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a data-toggle="tab" href="#push">Global Functions</a></li>
                        <li role="presentation" ><a data-toggle="tab" href="#post">Posts</a></li>
                        <li role="presentation" ><a data-toggle="tab" href="#page">Pages</a></li>
                        <li role="presentation" ><a data-toggle="tab" href="#media">Media</a></li>
                        <li role="presentation" ><a data-toggle="tab" href="#theme">Themes</a></li>
                        <li role="presentation" ><a data-toggle="tab" href="#plugin">Plugins</a></li>
                        <li role="presentation" ><a data-toggle="tab" href="#user">Users</a></li>
                        <li role="presentation" ><a data-toggle="tab" href="#custom_post">Custom Posts</a></li>
                        <li role="presentation" ><a data-toggle="tab" href="#menu">Menu</a></li>
                    </ul>
                    <div class="tab-content tabs-container">
                        <div id="push" role="tabpanel" class="tab-pane active" >
                            <div class="panel-body">
                                <input type="button" name="submit" value="Copy All" class="btn btn-success push-btn" id = "pub_all" />
                                <input type="button" name="submit" value="Copy All Excepts Post" class="btn btn-primary push-btn" id = "pub_all_post" />
                                <input type="button" name="submit" value="Copy Uploads" class="btn btn-primary push-btn" id = "pub_image" />
                                <input type="button" name="submit" value="Delete Destination" class="btn btn-danger push-btn" id = "pub_delete" />
                                <input type="button" name="submit" value="Sync Push Status" class="btn btn-primary push-btn" id = "sync" />
                            </div>
                        </div>
                        <div id="post" role="tabpanel" class="tab-pane">
                            <div class="panel-body">
                                <table class="table table-striped table-bordered dt-responsive nowrap" cellpadding="0" cellspacing="0" border="0" id="post_table">
                                    <thead>
                                        <tr>
                                            <th width="5%" align="left"><input type="checkbox" name="checkall" class="checkall-btn" id="checkall_post" /></th>
                                            <th align="left">Post Name</th>
                                            <th align="left">Category</th>
                                            <th align="left">Attachment</th>
                                            <th align="left">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="dataTables_empty">Loading data from server</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="page" role="tabpanel" class="tab-pane">
                            <div class="panel-body">
                            <table class="table table-striped table-bordered dt-responsive nowrap"  cellpadding="0" cellspacing="0" border="0" id="page_table" class="display" width="100%">
                            <thead>
                                <tr>
                                    <th width="10" align="left"><input type="checkbox" name="checkall" class="checkall-btn"  id="checkall_page" /></th>
                                    <th align="left">Page Name</th>
                                    <th align="left">Attachment</th>
                                    <th align="left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="3" class="dataTables_empty">Loading data from server</td>
                                </tr>
                            </tbody>
                            </table>
                            </div>
                        </div>
                        <div id="media" role="tabpanel" class="tab-pane">
                            <div class="panel-body">
                            <table class="table table-striped table-bordered dt-responsive nowrap"  id="media_table"  cellpadding="0" cellspacing="0" border="0">
                                <thead>
                                    <tr>
                                        <th width="5%" align="left"><input type="checkbox" name="checkall" class="checkall-btn"  id="checkall_media"></th>
                                        <th align="left">Thumbnail</th>
                                        <th align="left">Attachment Name</th>
                                        <th align="left">Attached To</th>
                                        <th align="left">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="dataTables_empty">Loading data from server</td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                        </div>
                        <div id="theme" role="tabpanel" class="tab-pane">
                            <div class="panel-body">
                                <table class="table table-striped table-bordered dt-responsive nowrap"  id="theme_table"  cellpadding="0" cellspacing="0" border="0">
                                <thead>
                                    <tr>
                                    <th align="left"><input type="checkbox" name="checkall" class="checkall-btn" id="checkall_theme">&nbsp;Theme Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                
                                $path = get_theme_root();
                                if ($handle = opendir($path)) {
                                    while (false !== ($entry = readdir($handle))) {
                                        if(is_dir($path.'/'.$entry)) {
                                            if ((strcmp($entry,'.') != 0) && (strcmp($entry,'..') != 0)){
                                ?>
                                            
                                        <tr><td align="left"><input type="checkbox" value="<?php echo $entry; ?>" name="themes[]" class = "theme_check" onclick="row_click(this,'theme')" />&nbsp;				
                                        <?php 
                                            echo $entry; 
                                        ?>
                                            </td>
                                            </tr>
                                <?php
                                        
                                            }
                                        }
                                    }
                                closedir($handle);
                                }
                                ?>
                                </tbody>
                                </table>
                            </div>
                            
                        </div>
                        <div id="plugin" role="tabpanel" class="tab-pane">
                            <div class="panel-body">
                            <table class="table table-striped table-bordered dt-responsive nowrap"  id="plugin_table"  cellpadding="0" cellspacing="0" border="0">
                            <thead>
                                <tr>
                                    <th align="left"><input type="checkbox" name="checkall" class="checkall-btn"  id="checkall_plugin">&nbsp;Plugin Name</th>
                                
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            
                            $path = ABSPATH."wp-content/plugins/";
                            if ($handle = opendir($path)) {
                            
                                while (false !== ($entry = readdir($handle))) {
                                    if(is_dir($path.'/'.$entry)) {
                                        if ((strcmp($entry,'.') != 0) && (strcmp($entry,'..') != 0)) {
                            ?>
                                        
                                    <tr><td align="left"><input type="checkbox" value="<?php echo $entry; ?>" name="plugins[]" class="checkall-btn"  class = "plugin_check" onclick="row_click(this,'plugin')" />&nbsp;<?php 
                                        echo $entry; ?>
                                        </td>
                                        
                                        </tr>
                            <?php
                                            
                                        }
                                    }
                                }
                                closedir($handle);
                            }
                            ?>
                            </tbody>
                            </table>
                            </div>
                            
                        </div>
                        <div id="user" role="tabpanel" class="tab-pane">
                            <div class="panel-body">
                            <table class="table table-striped table-bordered dt-responsive nowrap"  id="user_table"  cellpadding="0" cellspacing="0" border="0">
                            <thead>
                            <tr>
                                <th width="5%" align="left"><input type="checkbox" name="checkall" class="checkall-btn"  id="checkall_user"></th>
                                <th align="left">Username</th>
                                <th align="left">User Email</th>
                                <th align="left">Role</th>
                                <th align="left">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="dataTables_empty">Loading data from server</td>
                                </tr>
                            </tbody>
                            </table>
                            </div>
                        </div>
                        <div id="custom_post" role="tabpanel" class="tab-pane">
                            <div class="panel-body">
                            <table class="table table-striped table-bordered dt-responsive nowrap"  cellpadding="0" cellspacing="0" border="0" id="custom_post_table">
                            <thead>
                                <tr>
                                    <th width="5%" align="left"><input type="checkbox" name="checkall" class="checkall-btn" id="checkall_custom_post"></th>
                                    <th align="left">Post Name</th>
                                    <th align="left">Post Type</th>
                                    <th align="left">Category</th>
                                    <th align="left">Attachment</th>
                                    <th align="left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                    <tr>
                                        <td colspan="5" class="dataTables_empty">Loading data from server</td>
                                    </tr>
                            </tbody>
                            </table>
                            </div>
                            
                        </div>
                        <div id="menu" role="tabpanel" class="tab-pane">
                            <div class="panel-body">
                            <table class="table table-striped table-bordered dt-responsive nowrap"  cellpadding="0" cellspacing="0" border="0" id="menu_table">
                            <thead>
                                <tr>
                                    <th width="5%" align="left"><input type="checkbox" name="checkall" class="checkall-btn" id="checkall_menu"></th>
                                    <th align="left">Menu Name</th>
                                    <th align="left">Menu Slug</th>
                                    <th align="left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="dataTables_empty">Loading data from server</td>
                                </tr>
                            </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="dialog" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel" style="display: inline-block;">Processing</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="progress push-progress">
                        <div class="progress-bar progress-bar-info progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                            <span class="sr-only">0%</span>
                        </div>
                        </div>
                        <div id="log-container">
                        <table id = "ajax_field" class="table table-bordered table-striped">
                        </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                    </div>
                </div>
                </div>
                
                
                <div id="preview_image">
                    <img id = "pre_img" src="" alt="" />
                </div>
            </div>
        </div>
    </div>
    
</div>