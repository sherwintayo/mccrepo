<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href=".././bootstrap/css/styles.css" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" integrity="sha512-6PM0qYu5KExuNcKt5bURAoT6KCThUmHRewN3zUFNaoI6Di7XJPTMoT6K0nsagZKk2OB4L7E3q1uQKHNHd4stIQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->

<!-- Bootstrap core JavaScript
    ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<!-- <script type="text/javascript" src="../../bootstrap/js/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="../../bootstrap/js/bootstrap.bundle.min.js"></script> -->
<style>
    .img-top {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px;
        width: 150px;
    }

    .book-item .img-holder {
        width: 100%;
        height: 25em;
    }

    .book-item .img-holder>img.img-top {
        width: 100%;
        height: 40%;
        object-fit: cover;
        object-position: center center;
        transition: all .2s ease-in-out;
    }

    .book-item:hover .img-holder>img.img-top {
        transform: scale(1.2);
    }

    .truncate-5 {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 5;
        -webkit-box-orient: vertical;
    }
</style>
<?php
$count = 0;
$limit = 10;
$page = isset($_GET['p']) ? $_GET['p'] : 1;
$offset = 10 * ($page - 1);
$paginate = " limit {$limit} offset {$offset}";
$isSearch = isset($_GET['q']) ? "&q={$_GET['q']}" : "";
$search = "";

if (isset($_GET['q'])) {
    $keyword = $conn->real_escape_string($_GET['q']);
    $search = " and (title LIKE '%{$keyword}%' or abstract  LIKE '%{$keyword}%' or members LIKE '%{$keyword}%' 
                 or curriculum_id in (SELECT id FROM curriculum_list where name  LIKE '%{$keyword}%' or description  LIKE '%{$keyword}%') 
                 or curriculum_id in (SELECT id FROM curriculum_list where program_id in (SELECT id FROM program_list where name  LIKE '%{$keyword}%' or description  LIKE '%{$keyword}%'))) ";
}

$students = $conn->query("SELECT * FROM `student_list` WHERE id IN (SELECT student_id FROM archive_list WHERE `status` = 1 {$search})");
$student_arr = array_column($students->fetch_all(MYSQLI_ASSOC), 'email', 'id');
$count_all = $conn->query("SELECT * FROM archive_list WHERE `status` = 1 {$search}")->num_rows;
$pages = ceil($count_all / $limit);
$archives = $conn->query("SELECT * FROM archive_list WHERE `status` = 1 {$search} ORDER BY unix_timestamp(date_created) DESC {$paginate}");
?>

<div class="content project-container py-2 h-100">
    <div class="col-12">
        <div class="head">
            <h2 class="text-center">Published Project List</h2>
        </div>

        <div class="row">
            <?php while ($row = $archives->fetch_assoc()):
                $row['abstract'] = strip_tags(html_entity_decode($row['abstract'])); ?>
                <div class="cards col-lg-3 col-md-4 mb-2">
                    <a href="<?php echo base_url ?>admin/?page=projects/view_project&id=<?= $row['id'] ?>"
                        class="shadow book-item text-decoration-none">
                        <div class="img-holder banner overflow-hidden">
                            <img class="img-top" src="<?= validate_image($row['banner_path']) ?>" alt="Banner Image">
                        </div>
                        <div class="cards-body">
                            <div class="cards-title fw-bolder h5 text-center"><?= $row['title'] ?></div><br>
                            <div class="student">
                                <small>By:
                                    <b><?= isset($student_arr[$row['student_id']]) ? $student_arr[$row['student_id']] : "N/A" ?></b>
                                </small>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="card-footer clearfix rounded-0">
            <div class="d-flex justify-content-center">
                <ul class="pagination pagination-sm">
                    <li class="page-item">
                        <a class="page-link" href="./?page=projects<?= $isSearch ?>&p=<?= $page - 1 ?>" <?= $page == 1 ? 'disabled' : '' ?>>«</a>
                    </li>
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                            <a class="page-link" href="./?page=projects<?= $isSearch ?>&p=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item">
                        <a class="page-link" href="./?page=projects<?= $isSearch ?>&p=<?= $page + 1 ?>" <?= $page == $pages ? 'disabled' : '' ?>>»</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php // if(isset($conn)) { mysqli_close($conn); } ?>