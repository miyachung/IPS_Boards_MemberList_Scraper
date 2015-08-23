<?php
/*
* IPS Member List Scraper
* Coded by Miyachung
* Contact(Skype) : live:miyachung
* Janissaries.Org
*/
set_time_limit(0);
 
// DEFINED
define(SITENAME,"SITE NAME");
define(MEMBER_PROFILE_PATH,"/"); # DEFAULT
define(USERNAME,"FORUM USERNAME");
define(PASSWORD,"FORUM PASSWORD");
define(AUTH_KEY_REGEX1,'#<input type="hidden" name="auth_key" value="(.*?)">#');
define(AUTH_KEY_REGEX2,'#<input type="hidden" name="auth_key" value="(.*?)" />#');
define(AUTH_KEY_REGEX3,"#<input type='hidden' name='auth_key' value='(.*?)'>#");
define(AUTH_KEY_REGEX4,"#<input type='hidden' name='auth_key' value='(.*?)' />#");
define(USERS_REGEX,'@user\/(.*?)\/@');
 
 
$site_page      = login_site();
if(preg_match("/do=logout/",$site_page))
{
    $collected_users  = array();
    $queue              = array();
   
    echo "[ * ] Successfully Logged in to ".SITENAME."\n";
    $home_page_users =  Grab_Users($site_page);
    echo "[ * ] Count! ".count($home_page_users)." Found at Homepage\n";
    echo "[ * ] Scraper starting to to spread..\n";
    foreach($home_page_users as $user)
    {
        $visit_user = Visit_Users($user);
        $visit_grab = Grab_Users($visit_user);
        foreach($visit_grab as $scrap_user)
        {
            $scrap_user = Clear_Text($scrap_user);
            if(!in_array($scrap_user,$queue))
            {
                echo "Username found -> ".$scrap_user."\n";
                $queue[] = $scrap_user;
                $exp_quser = explode("-",$scrap_user);
                SaveToFile($exp_quser[1]);
            }
        }
        $explode_user      = explode("-",$user);
        if(!in_array($explode_user[1],$collected_users))
        {
            $collected_users[] = $explode_user[1];
            SaveToFile($explode_user[1]);
        }
    }
    $found_usernames = 0;
    while( count($queue) > 0 )
    {
        foreach($queue as $id => $quser)
        {
            $visit_user = Visit_Users($quser);
            $visit_grab = Grab_Users($visit_user);
            foreach($visit_grab as $scrap_user)
            {
                $scrap_user = Clear_Text($scrap_user);
                if(!in_array($scrap_user,$queue))
                {
                    $found_usernames++;
                    echo "[TOTAL $found_usernames] Username found -> ".$scrap_user."\n";
                    $queue[]  = $scrap_user;
                    $exp_quser = explode("-",$scrap_user);
                    SaveToFile($exp_quser[1]);
                }
            }
            $explode_user      = explode("-",$user);
            $explode_user[1]  = Clear_Text($explode_user[1]);
            if(!in_array($explode_user[1],$collected_users))
            {
                $collected_users[] = $explode_user[1];
                SaveToFile($explode_user[1]);
            }
            unset($queue[$id]);
        }
    }
   
   
}
else
{
    die("LOGIN FAILED - CHECK YOUR USERNAME AND PASSWORD");
}
 
 
 
 
Function login_site()
{
    echo "[ * ] Trying to get auth key to login..\n";
    $key  = grab_authkey();
    echo "[ * ] Auth Key : $key\n";
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl,CURLOPT_URL,SITENAME."/index.php?app=core&module=global&section=login&do=process");
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
    curl_setopt($curl,CURLOPT_COOKIEFILE,'site.cookie');
    curl_setopt($curl,CURLOPT_COOKIEJAR,'site.cookie');
    curl_setopt($curl,CURLOPT_POST,1);
    curl_setopt($curl,CURLOPT_POSTFIELDS,"auth_key=".$key."&ips_username=".USERNAME."&ips_password=".PASSWORD."&rememberMe=1");
    curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
    curl_setopt($curl,CURLOPT_HTTPHEADER,array("X-Forwarded-For" => rand(1,254).".".rand(1,254).".".rand(1,254).".".rand(1,254),"User-Agent" => "User-Agent: Mozilla/5.0 (Windows NT 6.1; ".rand(1,254).".".rand(1,254).".".rand(1,254).".".rand(1,254).";WOW64; rv:39.0) Gecko/20100101 Firefox/39.0"));
    $exec = curl_exec($curl);
    curl_close($curl);
    return $exec;
}
Function grab_authkey()
{
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl,CURLOPT_URL,SITENAME."/index.php?app=core&module=global&section=login");
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
    curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
    curl_setopt($curl,CURLOPT_HTTPHEADER,array("X-Forwarded-For" => rand(1,254).".".rand(1,254).".".rand(1,254).".".rand(1,254),"User-Agent" => "User-Agent: Mozilla/5.0 (Windows NT 6.1; ".rand(1,254).".".rand(1,254).".".rand(1,254).".".rand(1,254).";WOW64; rv:39.0) Gecko/20100101 Firefox/39.0"));
    curl_setopt($curl,CURLOPT_COOKIEJAR,'site.cookie');
    $exec = curl_exec($curl);
    curl_close($curl);
   
    if(!preg_match(AUTH_KEY_REGEX1,$exec,$key))
    {
        if(!preg_match(AUTH_KEY_REGEX2,$exec,$key))
        {
            if(!preg_match(AUTH_KEY_REGEX3,$exec,$key))
            {
                if(!preg_match(AUTH_KEY_REGEX4,$exec,$key))
                {
                   
                }
            }
        }
    }
   
    if($key[1])
    {
        return $key[1];
    }
    else
    {
        die("[ - ] Auth Key Grab Failed :'(");
    }
}
Function Grab_Users( $page )
{
    preg_match_all(USERS_REGEX, $page , $users );
    $users[1] = array_filter($users[1]);
    $users[1] = array_unique($users[1]);
    return $users[1];
}
Function Visit_Users( $username )
{
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl,CURLOPT_URL,SITENAME.MEMBER_PROFILE_PATH."user/$username");
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
    curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
    curl_setopt($curl,CURLOPT_COOKIEFILE,'joduska.cookie');
    $exec = curl_exec($curl);
    curl_close($curl);
    return $exec;
}
Function Clear_Text ( $text )
{
    $text = str_replace('"','',$text);
    $text = str_replace("'",'',$text);
    $text = str_replace("<","",$text);
    $text = str_replace(">","",$text);
    $text = trim($text);
    return $text;
}
Function SaveToFile($text)
{   
    $control = @explode("\n",@file_get_contents("members.txt"));
    if(!in_array($text,$control))
    {
        $fopen      = fopen('members.txt','ab');
        fwrite($fopen,$text."\n");
        fclose($fopen);
    }
}
?>
