<!-- =========================
     Quizify Footer Start
========================= -->

<footer class="footer">

    <div class="footer-container">

        <!-- ABOUT -->
        <div class="footer-section">

            <h2 class="footer-logo">Quizify</h2>

            <p>
                Quizify is a modern online quiz platform specially designed
                for Computer Science students. Practice quizzes, improve
                coding skills, track performance, and earn certificates.
            </p>

            <div class="social-icons">

                <a href="#">
                    🌐
                </a>

                <a href="#">
                    📘
                </a>

                <a href="#">
                    📸
                </a>

                <a href="#">
                    ▶
                </a>

            </div>

        </div>


        <!-- QUICK LINKS -->
        <div class="footer-section">

            <h3>Quick Links</h3>

            <ul>

                <li>
                    <a href="/quiz_system/index.php">
                        Home
                    </a>
                </li>

                <li>
                    <a href="/quiz_system/dashboard/dashboard.php?page=quizzes">
                        Quizzes
                    </a>
                </li>

                <li>
                    <a href="/quiz_system/dashboard/dashboard.php?page=performance">
                        Performance
                    </a>
                </li>

                <li>
                    <a href="/quiz_system/dashboard/dashboard.php?page=leaderboard">
                        Leaderboard
                    </a>
                </li>

                <li>
                    <a href="/quiz_system/dashboard/dashboard.php?page=my_certificates">
                        Certificates
                    </a>
                </li>

            </ul>

        </div>



        <!-- QUIZ CATEGORIES -->
        <div class="footer-section">

            <h3>Quiz Categories</h3>

            <ul class="category-list">

                <li>C Programming</li>
                <li>C++</li>
                <li>Java</li>
                <li>Python</li>
                <li>JavaScript</li>
                <li>DBMS</li>
                <li>Operating System</li>
                <li>Computer Networks</li>
                <li>Data Structures</li>
                <li>Computer Fundamentals</li>
                <li>Web Development</li>
                <li>Cyber Security</li>

            </ul>

        </div>



        <!-- CONTACT -->
        <div class="footer-section">

            <h3>Contact Us</h3>

            <p>
                📧 gyash4376@gmail.com
            </p>

            <p>
                📧 arjabj277@gmail.com
            </p>

            <p>
                📞 +91 6395918462
            </p>

            <p>
                📞 +91 7906001327
            </p>

            <p>
                📍 Gwalior, Madhya Pradesh
            </p>

        </div>

    </div>



    <!-- FOOTER BOTTOM -->

    <div class="footer-bottom">

        <p>
            © 2027 Quizify | All Rights Reserved
        </p>

    </div>

</footer>


<!-- =========================
     FOOTER CSS
========================= -->

<style>

.footer{

    background: linear-gradient(135deg,#0f172a,#1e293b);

    color:white;

    padding:60px 20px 20px;

    margin-top:60px;

    font-family:'Segoe UI',sans-serif;
}



/* CONTAINER */

.footer-container{

    max-width:1300px;

    margin:auto;

    display:grid;

    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));

    gap:40px;
}



/* LOGO */

.footer-logo{

    font-size:34px;

    color:#38bdf8;

    margin-bottom:15px;
}



/* TEXT */

.footer-section p{

    color:#cbd5e1;

    line-height:1.8;

    font-size:15px;
}



/* HEADINGS */

.footer-section h3{

    margin-bottom:18px;

    color:#ffffff;

    font-size:22px;
}



/* LIST */

.footer-section ul{

    list-style:none;

    padding:0;
}



/* LIST ITEMS */

.footer-section ul li{

    margin-bottom:12px;

    color:#cbd5e1;

    transition:0.3s;
}



/* LINKS */

.footer-section ul li a{

    text-decoration:none;

    color:#cbd5e1;

    transition:0.3s;
}



.footer-section ul li a:hover{

    color:#38bdf8;

    padding-left:6px;
}



/* CATEGORY GRID */

.category-list{

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:8px;
}



/* SOCIAL ICONS */

.social-icons{

    margin-top:20px;

    display:flex;

    gap:12px;
}



.social-icons a{

    width:40px;

    height:40px;

    border-radius:50%;

    background:rgba(255,255,255,0.08);

    display:flex;

    align-items:center;

    justify-content:center;

    text-decoration:none;

    font-size:18px;

    transition:0.3s;
}



.social-icons a:hover{

    background:#38bdf8;

    transform:translateY(-5px);
}



/* FOOTER BOTTOM */

.footer-bottom{

    text-align:center;

    border-top:1px solid rgba(255,255,255,0.1);

    margin-top:45px;

    padding-top:18px;

    color:#94a3b8;

    font-size:14px;
}



/* RESPONSIVE */

@media(max-width:900px){

    .footer-container{

        grid-template-columns:1fr 1fr;
    }
}



@media(max-width:600px){

    .footer-container{

        grid-template-columns:1fr;

        text-align:center;
    }

    .social-icons{

        justify-content:center;
    }

    .category-list{

        grid-template-columns:1fr;
    }

    .footer{

        padding:45px 15px 15px;
    }
}

</style>

<!-- =========================
     Quizify Footer End
========================= -->