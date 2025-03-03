<link rel="stylesheet" href="<?php echo base_url ?>myStyles/projects.css?v=<?php echo time(); ?>">

<div class="content project-container py-2 h-100">
    <div class="col-12">
        <div class="head d-flex align-items-center justify-content-between px-3">
            <h2 class="text-start flex-grow-1">Published Projects</h2>

            <!-- Year Filter -->
            <div class="form-group me-3">
                <label for="year" class="control-label text-white">Filter by Year</label>
                <select name="year" id="year" class="form-control form-control-border" onchange="filterByYear()">
                    <?php
                    // Fetch all distinct years from archive_list and determine the latest year
                    $years = $conn->query("SELECT DISTINCT `year` FROM archive_list WHERE `status` = 1 ORDER BY `year` DESC");
                    $latestYear = null;

                    while ($yearRow = $years->fetch_assoc()):
                        if (!$latestYear) {
                            $latestYear = $yearRow['year']; // Assign the first year as the latest
                        }
                        ?>
                        <option value="<?= $yearRow['year'] ?>" <?= isset($_GET['year']) && $_GET['year'] == $yearRow['year'] ? 'selected' : ($yearRow['year'] == $latestYear ? 'selected' : '') ?>>
                            <?= $yearRow['year'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Sort By Filter -->
            <div class="form-group">
                <label for="sort" class="control-label text-white">Sort By</label>
                <select name="sort" id="sort" class="form-control form-control-border" onchange="sortBy()">
                    <option value="" <?= !isset($_GET['sort']) ? 'selected' : '' ?>>Default</option>
                    <option value="title_asc" <?= isset($_GET['sort']) && $_GET['sort'] == 'title_asc' ? 'selected' : '' ?>>Title (A-Z)</option>
                    <option value="title_desc" <?= isset($_GET['sort']) && $_GET['sort'] == 'title_desc' ? 'selected' : '' ?>>Title (Z-A)</option>
                    <option value="date_desc" <?= isset($_GET['sort']) && $_GET['sort'] == 'date_desc' ? 'selected' : '' ?>>Date (Newest to Oldest)</option>
                    <option value="date_asc" <?= isset($_GET['sort']) && $_GET['sort'] == 'date_asc' ? 'selected' : '' ?>>
                        Date (Oldest to Newest)</option>
                </select>
            </div>
        </div>

        <?php
        // Set the limit to 100 without pagination
        $limit = 100;

        // Use the latest year as the default if no year is selected
        $selectedYear = isset($_GET['year']) ? $conn->real_escape_string($_GET['year']) : $latestYear;
        $yearFilter = " and `year` = '{$selectedYear}'";

        $isSearch = isset($_GET['q']) ? "&q={$_GET['q']}" : "";

        $search = "";
        if (isset($_GET['q'])) {
            $keyword = $conn->real_escape_string($_GET['q']);
            $search = " and (title LIKE '%{$keyword}%' or abstract LIKE '%{$keyword}%' or members LIKE '%{$keyword}%' or curriculum_id in (SELECT id FROM curriculum_list WHERE name LIKE '%{$keyword}%' or description LIKE '%{$keyword}%') or curriculum_id in (SELECT id FROM curriculum_list WHERE program_id in (SELECT id FROM program_list WHERE name LIKE '%{$keyword}%' or description LIKE '%{$keyword}%'))) ";
            $conn->query("INSERT INTO keyword_search_counter (keyword) VALUES ('{$keyword}')");
        }

        // Sort logic
        $sortBy = "";
        if (isset($_GET['sort'])) {
            switch ($_GET['sort']) {
                case 'title_asc':
                    $sortBy = "ORDER BY title ASC";
                    break;
                case 'title_desc':
                    $sortBy = "ORDER BY title DESC";
                    break;
                case 'date_asc':
                    $sortBy = "ORDER BY unix_timestamp(date_created) ASC";
                    break;
                case 'date_desc':
                    $sortBy = "ORDER BY unix_timestamp(date_created) DESC";
                    break;
            }
        }

        // Fetch students and archives with year filter and sort
        $students = $conn->query("SELECT * FROM `student_list` WHERE id IN (SELECT student_id FROM archive_list WHERE `status` = 1 {$search} {$yearFilter})");
        $student_arr = array_column($students->fetch_all(MYSQLI_ASSOC), 'lastname', 'id');

        $archives = $conn->query("SELECT * FROM archive_list WHERE `status` = 1 {$search} {$yearFilter} {$sortBy} LIMIT {$limit}");
        ?>

        <div class="row">
            <?php
            if ($archives->num_rows > 0):
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
                                        <b><?= isset($student_arr[$row['student_id']]) ? $student_arr[$row['student_id']] : "N/A" ?></b></small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php
                endwhile;
            else:
                ?>
                <div class="col-12">
                    <p class="text-center text-muted">No projects found for the selected filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // JavaScript to handle year filter change
    function filterByYear() {
        const year = document.getElementById('year').value;
        const params = new URLSearchParams(window.location.search);
        params.set('year', year);
        window.location.search = params.toString();
    }

    // JavaScript to handle sort filter change
    function sortBy() {
        const sort = document.getElementById('sort').value;
        const params = new URLSearchParams(window.location.search);
        if (sort) {
            params.set('sort', sort);
        } else {
            params.delete('sort');
        }
        window.location.search = params.toString();
    }
</script>
















<!-- <div class="content py-2">
    <div class="col-12">
        <div class="card card-outline card-primary shadow rounded-0">
            <div class="card-body rounded-0">
                <h2>Archive List</h2>
                <hr class="bg-navy">
                <?php
                $limit = 10;
                $page = isset($_GET['p']) ? $_GET['p'] : 1;
                $offset = 10 * ($page - 1);
                $paginate = " limit {$limit} offset {$offset}";
                $isSearch = isset($_GET['q']) ? "&q={$_GET['q']}" : "";

                $search = "";
                if (isset($_GET['q'])) {
                    $keyword = $conn->real_escape_string($_GET['q']);
                    $search = " and (title LIKE '%{$keyword}%' or abstract  LIKE '%{$keyword}%' or members LIKE '%{$keyword}%' or curriculum_id in (SELECT id from curriculum_list where name  LIKE '%{$keyword}%' or description  LIKE '%{$keyword}%') or curriculum_id in (SELECT id from curriculum_list where program_id in (SELECT id FROM program_list where name  LIKE '%{$keyword}%' or description  LIKE '%{$keyword}%'))) ";

                    //keuword_count count exec
                    $conn->query("INSERT INTO keyword_search_counter (keyword) VALUES ('{$keyword}')");
                }
                $students = $conn->query("SELECT * FROM `student_list` where id in (SELECT student_id FROM archive_list where `status` = 1 {$search})");
                $student_arr = array_column($students->fetch_all(MYSQLI_ASSOC), 'lastname', 'id');
                $count_all = $conn->query("SELECT * FROM archive_list where `status` = 1 {$search}")->num_rows;
                $pages = ceil($count_all / $limit);
                $archives = $conn->query("SELECT * FROM archive_list where `status` = 1 {$search} order by unix_timestamp(date_created) desc {$paginate}");
                ?>
                <?php if (!empty($isSearch)): ?>
                <h3 class="text-center"><b>Search Result for "<?= $keyword ?>" keyword</b></h3>
                <?php endif ?>
                <div class="list-group">
                    <?php
                    while ($row = $archives->fetch_assoc()):
                        $row['abstract'] = strip_tags(html_entity_decode($row['abstract']));
                        ?>
                    <a href="./?page=view_archive&id=<?= $row['id'] ?>" class="text-decoration-none text-dark list-group-item list-group-item-action">
                        <div class="row">
                            <div class="col-lg-4 col-md-5 col-sm-12 text-center">
                                <img src="<?= validate_image($row['banner_path']) ?>" class="banner-img img-fluid bg-gradient-dark" alt="Banner Image">
                            </div>
                            <div class="col-lg-8 col-md-7 col-sm-12">
                                <h3 class="text-navy"><b><?php echo $row['title'] ?></b></h3>
                                <small class="text-muted">By <b class="text-info"><?= isset($student_arr[$row['student_id']]) ? $student_arr[$row['student_id']] : "N/A" ?></b></small>
                                <p class="truncate-5"><?= $row['abstract'] ?></p>
                            </div>
                        </div>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
            <div class="card-footer clearfix rounded-0">
                <div class="col-12">
                    <div class="row">
                        <div class="col-md-6"><span class="text-muted">Display Items: <?= $archives->num_rows ?></span></div>
                        <div class="col-md-6">
                            <ul class="pagination pagination-sm m-0 float-right">
                                <li class="page-item"><a class="page-link" href="./?page=projects<?= $isSearch ?>&p=<?= $page - 1 ?>" <?= $page == 1 ? 'disabled' : '' ?>>«</a></li>
                                <?php for ($i = 1; $i <= $pages; $i++): ?>
                                <li class="page-item"><a class="page-link <?= $page == $i ? 'active' : '' ?>" href="./?page=projects<?= $isSearch ?>&p=<?= $i ?>"><?= $i ?></a></li>
                                <?php endfor; ?>
                                <li class="page-item"><a class="page-link" href="./?page=projects<?= $isSearch ?>&p=<?= $page + 1 ?>" <?= $page == $pages ? 'disabled' : '' ?>>»</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div> -->