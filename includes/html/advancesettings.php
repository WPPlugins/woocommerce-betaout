<?php
if(isset($_POST['submit'])){
   $track_searches=isset($_POST['track_searches'])?true : false;
   $track_posts=isset($_POST['track_posts'])?true : false;
   $track_pages=isset($_POST['track_pages'])?true : false;
   $track_archives=isset($_POST['track_archives'])?true : false;
   $opengraphtag=isset($_POST['opengraphtag'])?true : false;
   $twittercard=isset($_POST['twittercard'])?true : false;
   update_option('amplify_track_searches',$track_searches);
   update_option('amplify_track_posts',$track_posts);
   update_option('amplify_track_pages',$track_pages);
   update_option('amplify_track_archives',$track_archives);
   update_option('amplify_opengraphtag',$opengraphtag);
   update_option('amplify_twittercard',$twittercard);
}
?>

<div class="well-bx-outer">
    <div class=" form-group bread-cum">
        <ul>
            <img src="<?php echo plugins_url('images/amplify.png', dirname(dirname(__FILE__))); ?>"/><li><a href="">Amplify Apps</a></li> >
            <li><a href="">Advance Setting</a></li>
        </ul>

    </div>
    <h2>Advance Setting</h2>
    <div class="well-bx"  style="padding:50px;padding-top: 10px">
        <h3>Site Setting</h3>
        <form action="" method="post">
        <div class=" row well-bx-inner clear" >
            <div class="check-box pull-left">
                <input type="checkbox" name="opengraphtag" id="opengraphtag" value="1" <?php if (get_option('amplify_opengraphtag',false)) echo 'checked="checked"'; ?>/>
                <label>  Exclude Facbook Open Graph tag (it is recommended NOT to disable open graph tag)</label></div>

        </div>
        <div class=" row well-bx-inner clear" style=" border-bottom:0 ">
            <div class="check-box pull-left">
                <input type="checkbox" name="twittercard" id="twittercard" value="1" <?php if (get_option('amplify_twittercard',false)) echo 'checked="checked"'; ?>/>
                <label>  Exclude Twitter Card Integration (it is recommended NOT to disable Twitter Card Integration)</label></div>
         </div>
            <div class=" row well-bx-inner clear" style=" border-bottom:0 ">
            <div class="check-box pull-left">
                <input type="checkbox" name="track_posts" id="track_posts" value="1" <?php if (get_option('amplify_track_posts',false)) echo 'checked="checked"'; ?>/>
                <label> Track Posts- Automatically track events when your users view Posts. </label></div>
         </div>
            <div class=" row well-bx-inner clear" style=" border-bottom:0 ">
            <div class="check-box pull-left">
                <input type="checkbox" name="track_pages" id="track_pages" value="1" <?php if (get_option('amplify_track_pages',false)) echo 'checked="checked"'; ?>/>
                <label> Track Pages-  Automatically track events when your users view Pages. </label></div>
         </div>
             <div class=" row well-bx-inner clear" style=" border-bottom:0 ">
            <div class="check-box pull-left">
                <input type="checkbox" name="track_archives" id="track_archives" value="1" <?php if (get_option('amplify_track_archives',false)) echo 'checked="checked"'; ?>/>
                <label> Track Archives- Automatically track events when your users view archive pages. </label></div>
         </div>

          <div class=" row well-bx-inner clear" style=" border-bottom:0 ">
            <div class="check-box pull-left">
                <input type="checkbox" name="track_searches" id="track_searches" value="1" <?php if (get_option('amplify_track_searches',false)) echo 'checked="checked"'; ?>/>
                <label> Track Archives-  Automatically track events when your users view the search results page.. </label></div>
         </div>
            <div class=" row well-bx-inner clear" style=" border-bottom:0 ">
            <label> Twitter Card Name</label>
                <input type="text" name="amplify_twittername" id="track_searches" value="" <?php echo get_option('amplify_twittername',false)?>/>
               
           </div>
            <div class=" row well-bx-inner clear" style=" border-bottom:0 ">
               <label> Facebook App ID </label>
             <input type="text" name="amplify_fb_app_id" id="track_searches" value="" <?php echo get_option('amplify_fb_app_id',false)?>/>
            </div>
            <div class=" row well-bx-inner clear" style=" border-bottom:0 ">
                <label> Facebook Admin ID </label>
                <input type="text" name="amplify_facebook_admins" id="track_searches" value="" <?php echo get_option('amplify_facebook_admins',false)?>/>

           </div>
            <div class="check-box pull-left">
            <input class="button button-primary"
             type="submit"
             name="submit"
             id="submit"
             value="Save Changes" />
            </div>
        </form>
    </div>

</div>