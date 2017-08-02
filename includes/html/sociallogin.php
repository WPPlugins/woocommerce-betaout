<?php
if (isset($_POST['submit'])) {
    $amplify_loginprofile=isset($_POST['amplify_loginprofile']) ? true : false;
    $amplify_login_abovecomment = isset($_POST['amplify_login_abovecomment']) ? true : false;
    $amplify_login_abovecomment_widgetId = isset($_POST['amplify_login_abovecomment_widgetId']) ? $_POST['amplify_login_abovecomment_widgetId'] : false;
    $amplify_login_loginform = isset($_POST['amplify_login_loginform']) ? true : false;
    $amplify_login_loginform_widgetId = isset($_POST['amplify_login_loginform_widgetId']) ? $_POST['amplify_login_loginform_widgetId'] : false;
    $amplify_login_registration = isset($_POST['amplify_login_registration']) ? true : false;
    $amplify_login_registration_widgetId = isset($_POST['amplify_login_registration_widgetId']) ? $_POST['amplify_login_registration_widgetId'] : false;
    $amplify_login_sidebar = isset($_POST['amplify_login_sidebar']) ? true : false;
    $amplify_login_sidebar_widgetId = isset($_POST['amplify_login_sidebar_widgetId']) ? $_POST['amplify_login_sidebar_widgetId'] : false;

    update_option('amplify_loginprofile', $amplify_loginprofile);
    update_option('amplify_login_abovecomment', $amplify_login_abovecomment);
    update_option('amplify_login_abovecomment_widgetId', $amplify_login_abovecomment_widgetId);
    update_option('amplify_login_loginform', $amplify_login_loginform);
    update_option('amplify_login_loginform_widgetId', $amplify_login_loginform_widgetId);
    update_option('amplify_login_registration', $amplify_login_registration);
    update_option('amplify_login_registration_widgetId', $amplify_login_registration_widgetId);
    update_option('amplify_login_sidebar', $amplify_login_sidebar);
    update_option('amplify_login_sidebar_widgetId', $amplify_login_sidebar_widgetId);
}
?>

<div class="well-bx-outer" >
    <div class="row form-group">
        <div class="bread-cum pull-left">
            <ul>
                <img src="<?php echo plugins_url('images/amplify.png', dirname(dirname(__FILE__))); ?>"/><li><a href="">Amplify Apps</a></li> >
                <li><a href="">Advance Setting</a></li>
            </ul>
        </div>
        <a href="" class=" pull-right">
            <img src="<?php echo plugins_url('images/btn.png', dirname(dirname(__FILE__))); ?>"/>
        </a>
    </div>
    <div class="well-bx pull-left">
        <form class="form-inline" method="post" action="">
            <div class=" row form-group clear" >
                <div class="check-box pull-left">
                    <input type="checkbox" name="amplify_loginprofile" id="amplify_loginprofile" value="1" <?php if (get_option('amplify_loginprofile',false)) echo 'checked="checked"'; ?>/>
                    <label style=" font-size:18px "> Include Profile Snap (Recommended)</label>
                </div>
            </div>
            <div class="row form-group clear">
                <h1 class=" pull-left">Placements</h1> <div class=" pull-left" style="border-bottom:6px solid #333;width:450px; margin-left:30px;margin-top:35px"></div>
            </div>
            <div class="row form-group clear">
                <div class="check-box pull-left">
                    <input type="checkbox" name="amplify_login_abovecomment" id="amplify_login_abovecomment" value="1" <?php if (get_option('amplify_login_abovecomment',false)) echo 'checked="checked"'; ?>/><label> Above the Comment</label>
                </div>
            </div>
            <div class="row form-group clear">
                <label>Select a Design</label>
                <?php
                $amplifyObj = new Amplify();
                $result = $amplifyObj->fetchwidget('1');
                ?>
                <select style="width:140px;margin-left:20px " name="amplify_login_abovecomment_widgetId">
                    <?php
                    foreach ($result as $data) {?>
                       <option value="<?php echo $data->id ?>" <?php if(get_option('amplify_login_abovecomment_widgetId',false)==$data->id) echo 'selected="selected"';?> > <?php echo $data->widgetName ?></option>
                   <?php  }
                    ?>
                </select>

                <a href="" class=" pull-right" style=" width:100px;font-size:11px">Get more Design</a>
            </div>
            <div  class=" pull-right form-group clear" style=" margin-right:7% "><span>Preview</span></div>
            <div class="row form-group white-bx border-bottom clear">
                icons
            </div>

            <div class="row form-group border-bottom clear" >
                <div class="check-box pull-left">
                    <input type="checkbox" name="amplify_login_loginform" id="amplify_login_loginform" value="1" <?php if (get_option('amplify_login_loginform',false)) echo 'checked="checked"'; ?>/><label> On the login page</label>
                </div>
            </div>
            <div class="row form-group clear">
                <label>Select a Design</label>
                <?php
                    $amplifyObj = new Amplify();
                    $result = $amplifyObj->fetchwidget('1');
                ?>
                    <select style="width:140px;margin-left:20px" name="amplify_login_loginform_widgetId">
                    <?php
                    foreach ($result as $data) {?>
                        <option value="<?php echo $data->id ?>" <?php if(get_option('amplify_login_loginform_widgetId',false)==$data->id) echo 'selected="selected"';?> > <?php echo $data->widgetName ?></option>
                   <?php  }
                    ?>
                </select>

                <a href="" class=" pull-right" style=" width:100px;font-size:11px">Get more Design</a>
            </div>
            <div  class=" pull-right form-group clear" style=" margin-right:7% "><span>Preview</span></div>
            <div class="row form-group white-bx border-bottom clear">
                icons
            </div>

            <div class="row form-group border-bottom clear">
                <div class="check-box pull-left"> 
                    <input type="checkbox" name="amplify_login_registration" id="amplify_login_registration" value="1" <?php if (get_option('amplify_login_registration',false)) echo 'checked="checked"'; ?>/>
                    <label> On the registration page</label>

                </div>
            </div>
                <div class="row form-group clear">

                    <label>Select a Design</label>
                    <?php
                    $amplifyObj = new Amplify();
                    $result = $amplifyObj->fetchwidget('1');
                    ?>
                    <select style="width:140px;margin-left:20px " name="amplify_login_registration_widgetId">
                        <?php
                        foreach ($result as $data) {?>
                             <option value="<?php echo $data->id ?>" <?php if(get_option('amplify_login_registration_widgetId',false)==$data->id) echo 'selected="selected"';?> > <?php echo $data->widgetName ?></option>
                       <?php  }
                        ?>
                    </select>

                    <a href="" class=" pull-right" style=" width:100px;font-size:11px">Get more Design</a>
                </div>
                <div  class=" pull-right form-group clear" style=" margin-right:7% "><span>Preview</span></div>
                <div class="row form-group white-bx border-bottom clear">
                    icons
                </div>
            
            <div class="row form-group border-bottom clear">
                <div class="check-box pull-left">
                    <input type="checkbox" name="amplify_login_sidebar" id="amplify_login_sidebar" value="1" <?php if (get_option('amplify_login_registration',false)) echo 'checked="checked"'; ?>/>
                    <label> In your sidebar(as a widget)</label>
                </div>
            </div>
                <div class="row form-group clear">

                    <label>Select a Design</label>
                    <?php
                        $amplifyObj = new Amplify();
                        $result = $amplifyObj->fetchwidget('1');
                    ?>
                        <select style="width:140px;margin-left:20px " name="amplify_login_sidebar_widgetId">
                        <?php
                        foreach ($result as $data) {?>
                             <option value="<?php echo $data->id ?>" <?php if(get_option('amplify_login_sidebar_widgetId',false)==$data->id) echo 'selected="selected"';?> > <?php echo $data->widgetName ?></option>
                        <?php }
                        ?>
                    </select>
                    <a href="" class=" pull-right" style=" width:100px;font-size:11px">Get more Design</a>
                </div>
                <div class="row form-group white-bx border-bottom clear">
                    icons
                </div>
                <div class="row clear form-group">
                <input type="submit"  class="btn btn-info pull-right" value="Save" name="submit"/>
            </div>
            <div class="row form-group clear">
                <h1 class=" pull-left">Shortcode</h1> <div class=" pull-left" style="border-bottom:6px solid #333;width:450px; margin-left:30px;margin-top:35px"></div>
            </div>
            <div class="row form-group white-bx clear">
                icons
            </div>
        </form>
    </div>


</div>
