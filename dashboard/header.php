<div style="background:#e6f2ff; padding:15px 25px; display:flex; justify-content:space-between; align-items:center; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1);">

    <div>
        <h2 style="margin:0; color:#004080;">
            Welcome <?php echo $username; ?> 👋
        </h2>
        <p style="margin:5px 0 0; font-size:14px; color:#336699;">
            Ready to test your skills today?
        </p>
    </div>

    <div style="display:flex; align-items:center; gap:15px;">
        <div style="background:#007bff; color:white; padding:8px 14px; border-radius:20px; font-weight:bold;">
            <?php echo $username; ?>
        </div>

        <a href="../logout.php" 
           style="text-decoration:none; background:#ff4d4d; color:white; padding:8px 14px; border-radius:6px;">
           Logout
        </a>
    </div>

</div>