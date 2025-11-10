<?php
include 'includes/session.php';
include 'nav.php';
?>
<!DOCTYPE html>
<html>
<head>
<title>Main Menu</title>
<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(to bottom, #FFE0C0, #FF6600);
        min-height: 100vh;
        width: 100%;
        font-size: 13px;
        
        /* UBAHAN 1: Tambahkan padding-top ini
          (Ini disalin dari file nav.php Anda agar tidak tertimpa)
        */
        padding-top: 38px; 
        padding-bottom: 40px; /* Ruang untuk footer */
        box-sizing: border-box;
    }

    /* Layer background gambar + gradient */
    body::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background:
            url('pictures/allbackground.png') no-repeat center center,
            linear-gradient(to bottom, #FFE0C0, #FF6600);
        background-size: cover, cover;
        z-index: -2; /* UBAH: Dorong ke paling belakang */
    }

    /* Tulisan berjalan */
    .marquee {
        width: 100%;
        overflow: hidden;
        white-space: nowrap;
        box-sizing: border-box;
        padding: 8px 0;
        
        /* background: rgba(0, 0, 0, 0.4); <-- HAPUS BARIS INI */
        position: relative; 
        z-index: -1;      
    }
    .marquee span {
        display: inline-block;
        padding-left: 100%;
        animation: marquee 25s linear infinite;
        color: #32CD32;
        font-size: 2em;
        font-weight: bold;
        text-shadow: 3px 3px 6px rgba(0,0,0,0.7);
    }
    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-100%); }
    }

    /* Tulisan besar kiri tengah */
    .big-text {
        position: absolute;
        top: 50%;
        left: 50px;
        transform: translateY(-50%);
        color: #FF6600;
        font-size: 48px;
        font-weight: bold;
        line-height: 1.2;
        text-align: left;
    }
</style>
</head>
<body>

<!-- Tulisan berjalan -->
<div class="marquee">
    <span>The system will be Good if the Management of the Data is Good</span>
</div>

<!-- Tulisan besar kiri tengah -->
<div class="big-text">
    Rewrite<br>
    the<br>
    Future
</div>



</body>
</html>
