<?php
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM program_list where `status` = 1 and id = '{$_GET['id']}' ");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            if (!is_numeric($k)) {
                $program[$k] = $v;
            }
        }
    } else {
        echo "<script> alert('Unknown Program ID'); location.replace('./') </script>";
    }
} else {
    echo "<script> alert('Program ID is required'); location.replace('./') </script>";
}
?>
<link rel="stylesheet" href="<?php echo base_url ?>myStyles/projects.css?v=<?php echo time(); ?>">

<div class="content project-container py-2 h-100">
    <div class="col-12">
        <div class="head">
            <h2 class="text-center">Projects for <?= isset($program['name']) ? $program['name'] : "" ?></h2>
        </div>
        <p class="text-center"><small><?= isset($program['description']) ? $program['description'] : "" ?></small></p>
        <?php
        $id = isset($_GET['id']) ? $_GET['id'] : '';
        $limit = 10;
        $page = isset($_GET['p']) ? $_GET['p'] : 1;
        $offset = 10 * ($page - 1);
        $paginate = " limit {$limit} offset {$offset}";
        $wheredid = " where program_id = '{$id}' ";
        $students = $conn->query("SELECT * FROM `student_list` where id in (SELECT student_id FROM archive_list where `status` = 1 and curriculum_id in (SELECT id from curriculum_list {$wheredid} ))");
        $student_arr = array_column($students->fetch_all(MYSQLI_ASSOC), 'lastname', 'id');
        $count_all = $conn->query("SELECT * FROM archive_list where `status` = 1 and curriculum_id in (SELECT id from curriculum_list {$wheredid} )")->num_rows;
        $pages = ceil($count_all / $limit);
        $archives = $conn->query("SELECT * FROM archive_list where `status` = 1 and curriculum_id in (SELECT id from curriculum_list {$wheredid} ) order by unix_timestamp(date_created) desc {$paginate}");
        ?>
        <div class="row">
            <?php
            while ($row = $archives->fetch_assoc()):
                $row['abstract'] = strip_tags(html_entity_decode($row['abstract']));
                ?>
                <div class="cards col-lg-3 col-md-4 mb-2">
                    <a href="./?page=view_archive&id=<?= $row['id'] ?>" class="shadow book-item text-decoration-none">
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
                        <a class="page-link" href="./?page=projects_per_program&id=<?= $id ?>&p=<?= $page - 1 ?>"
                            <?= $page == 1 ? 'disabled' : '' ?>>«</a>
                    </li>
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                            <a class="page-link" href="./?page=projects_per_program&id=<?= $id ?>&p=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item">
                        <a class="page-link" href="./?page=projects_per_program&id=<?= $id ?>&p=<?= $page + 1 ?>"
                            <?= $page == $pages ? 'disabled' : '' ?>>»</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>