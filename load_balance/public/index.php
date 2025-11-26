<?php

session_start();
echo @$_SESSION['id'];
isset($_SESSION['id'])?'':$_SESSION['id']=rand(1,10);
echo filter_input(INPUT_SERVER, 'HTTP_NOTE', FILTER_DEFAULT) ?? null;
echo '<br><br><span>IP-REAL => ' . (filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_DEFAULT) ?? null) . '</span>';
echo '<br><span>IP-CONTAINER => ' . (filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_DEFAULT) ?? null) . '</span>';