    <?php
    if(isset($_POST['submit'])){
       $amplify_share_above_post=isset($_POST['amplify_share_above_post'])?true : false;
       $amplify_share_above_post_widgetId=isset($_POST['amplify_share_above_post_widgetId'])?$_POST['amplify_share_above_post_widgetId'] : false;
       $amplify_share_below_post=isset($_POST['amplify_share_below_post'])?true : false;
       $amplify_share_below_post_widgetId=isset($_POST['amplify_share_below_post_widgetId'])?$_POST['amplify_share_below_post_widgetId'] : false;
      
       update_option('amplify_share_above_post',$amplify_share_above_post);
       update_option('amplify_share_above_post_widgetId',$amplify_share_above_post_widgetId);
       update_option('amplify_share_below_post',$amplify_share_below_post);
       update_option('amplify_share_below_post_widgetId',$amplify_share_below_post_widgetId);
      
    }
    ?>

    <div  class="well-bx-outer">
        <div class="row form-group">
            <div class="bread-cum pull-left">
                <ul>
                    <img  src="<?php echo plugins_url('images/amplify.png', dirname(dirname(__FILE__))); ?>"/><li><a href="">Amplify Apps</a></li> >
                    <li><a href="">Advance Setting</a></li>
                </ul>
            </div>
            <a href="" class=" pull-right">
                <img src="<?php echo plugins_url('images/btn.png', dirname(dirname(__FILE__))); ?>"/>
            </a>
        </div>
        <div class="well-bx pull-left">
             <form class="form-inline " method="post" action="">
            <div class="row form-group clear">
                <div class="check-box pull-left">
                    <input type="checkbox" name="amplify_share_above_post" id="amplify_share_above_post" value="1" <?php if (get_option('amplify_share_above_post',false)) echo 'checked="checked"'; ?>/>
                    <label style=" font-size:18px ">Above the Post</label>
                </div>
                </div>
            <div class="row form-group clear" style=" margin-top:30px;margin-bottom:20px  ">
               
                    <label class=" control" >Select a Design</label>
                      <?php  $amplifyObj=new Amplify();
                            $result=$amplifyObj->fetchwidget('1');
                            ?>
                        <select style="width:140px;margin-left:20px " name="amplify_share_above_post_widgetId">
                        <?php foreach($result as $data){?>
                            <option value="<?php echo $data->id ?>" <?php if(get_option('amplify_share_above_post_widgetId',false)==$data->id) echo 'selected="selected"';?> > <?php echo $data->widgetName ?></option>
                        <?php }?>
                        </select>
           
                <a href="" class=" pull-right" style=" width:100px;font-size:11px">Get more Design</a>
            </div>
            <div  class=" pull-right form-group clear" style=" margin-right:7% "><span>Preview</span></div>
            <div class="row form-group white-bx border-bottom clear">
                <img src="/css-img/socialicon.png"/>
            </div>
            <div class="row form-group clear">
                <div class="check-box pull-left"> 
                    <input type="checkbox" name="amplify_share_below_post" id="amplify_share_below_post" value="1" <?php if (get_option('amplify_share_below_post',false)) echo 'checked="checked"'; ?>/>
                    <label style=" font-size:18px ">Below the Post</label></div>
            </div>
            <div class="row form-group clear" style=" margin-top:30px;margin-bottom:20px  ">
               <label class=" control" >Select a Design</label>
                    <?php  $amplifyObj=new Amplify();
                            $result1=$amplifyObj->fetchwidget('1');
                            ?>
                        <select style="width:140px;margin-left:20px " name="amplify_share_below_post_widgetId">
                           <?php foreach($result1 as $data){?>
                            <option value="<?php echo $data->id ?>" <?php if(get_option('amplify_share_below_post_widgetId',false)==$data->id) echo 'selected="selected"';?> > <?php echo $data->widgetName ?></option>
                        <?php }?>
                        </select>
                
                <a href="" class=" pull-right" style=" width:100px;font-size:11px">Get more Design</a>
            </div>
            <div class="row form-group white-bx border-bottom clear">
                <img src="/css-img/socialicon.png"/>
            </div>
           
             <div class="row clear form-group">
                <input type="submit"  class="btn btn-info pull-right" value="Save" name="submit"/>
            </div>
             </form>


            <div class="row form-group clear">
                <h1 class=" pull-left">Shortcode</h1> <div class=" pull-left" style="border-bottom:6px solid #333;width:450px; margin-left:30px;margin-top:35px"></div>
            </div>
            <div class="row form-group white-bx clear">
                icons
            </div>
        </div>

    </div>