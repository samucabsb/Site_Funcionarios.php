<?php
// =============================================================
//  logout.php - Encerrar sessao
// =============================================================
session_start();
session_destroy();
header('Location: login.php');
exit();
?>