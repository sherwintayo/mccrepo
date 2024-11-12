<head>
  <link rel="stylesheet" href="<?php echo base_url ?>myStyles/stdntprof_style.css?v=<?php echo time(); ?>">
  <style>
    .header__wrapper header {
      width: 100%;
      background: url("<?php echo validate_image($_settings->info('cover')) ?>") no-repeat 50% 20% / cover;
      min-height: calc(100px + 15vw);
    }
  </style>
</head>
<?php require_once('./config.php'); ?>
<?php
$user = $conn->query("SELECT s.*,d.name as program, c.name as curriculum,CONCAT(lastname,', ',firstname,' ',middlename) as fullname FROM student_list s inner join program_list d on s.program_id = d.id inner join curriculum_list c on s.curriculum_id = c.id where s.id ='{$_settings->userdata('id')}'");
foreach ($user->fetch_array() as $k => $v) {
  $$k = $v;
}
?>

<body>
  <div class="header__wrapper">
    <header></header>
    <div class="cols__container">
      <div class="left__col">
        <div class="img__container">
          <img src="<?= validate_image($avatar) ?>" alt="Student Image" />
          <span></span>
        </div>
        <h2><?= ucwords($fullname) ?></h2>
        <p>Programmer</p>
        <p><?= $email ?></p>

        <ul class="about">
          <li><span>4,073</span>Followers</li>
          <li><span>322</span>Following</li>
          <li><span>200,543</span>Attraction</li>
        </ul>

        <div class="content">
          <p>
            Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam
            erat volutpat. Morbi imperdiet, mauris ac auctor dictum, nisl
            ligula egestas nulla.
          </p>

          <ul>
            <li><i class="fab fa-twitter"></i></li>
            <i class="fab fa-pinterest"></i>
            <i class="fab fa-facebook"></i>
            <i class="fab fa-dribbble"></i>
          </ul>
        </div>
      </div>
      <div class="right__col">
        <nav>
          <ul>
            <li><a href="">my archives</a></li>
            <li><a href="">submit capstone projects</a></li>
            <li><a href="">notifications</a></li>
            <li><a href="">account settings</a></li>
          </ul>
        </nav>

        <div class="photos">
          <img src="img/img_1.avif" alt="Photo" />
          <img src="img/img_2.avif" alt="Photo" />
          <img src="img/img_3.avif" alt="Photo" />
          <img src="img/img_4.avif" alt="Photo" />
          <img src="img/img_5.avif" alt="Photo" />
          <img src="img/img_6.avif" alt="Photo" />
        </div>
      </div>
    </div>
  </div>
</body>