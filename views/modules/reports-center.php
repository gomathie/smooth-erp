<?php
/**
 * Reports Center - Centralized report hub
 */
require_once __DIR__ . '/../../controllers/reports_center.controller.php';

if (!Permission::has("reports") && !Permission::has("accounting")) {
    echo '<script>window.location = "home";</script>';
    return;
}

// Seed reports on first load
ControllerReportsCenter::ctrSeedReports();

// Handle favorites AJAX
if (isset($_POST["toggleFavorite"])) {
    header('Content-Type: application/json');
    $reportId = (int)$_POST["reportId"];
    $result = ControllerReportsCenter::ctrToggleFavorite($reportId);
    echo json_encode(array("favorited" => $result));
    exit;
}

// Handle view recording via AJAX
if (isset($_POST["recordView"])) {
    header('Content-Type: application/json');
    $reportId = (int)$_POST["reportId"];
    ControllerReportsCenter::ctrRecordView($reportId);
    echo json_encode(array("ok" => true));
    exit;
}

$categories  = ControllerReportsCenter::ctrGetCategories();
$favIds      = ControllerReportsCenter::ctrGetFavoriteIds();
$recent      = ControllerReportsCenter::ctrGetRecentReports(5);

$activeCat   = $_GET["cat"] ?? "";
$showFavs    = isset($_GET["favs"]);
$searchQ     = $_GET["q"] ?? "";

if ($searchQ !== "") {
    $reports = ControllerReportsCenter::ctrSearchReports($searchQ);
    $activeCat = "";
} elseif ($showFavs) {
    $allReports = ControllerReportsCenter::ctrGetAllReports();
    $reports = array_filter($allReports, function($r) use ($favIds) {
        return in_array((int)$r["id"], $favIds, true);
    });
    $reports = array_values($reports);
} elseif ($activeCat !== "") {
    $catInfo = null;
    foreach ($categories as $c) { if ($c["slug"] === $activeCat) { $catInfo = $c; break; } }
    $reports = $catInfo ? ControllerReportsCenter::ctrGetReportsByCategory((int)$catInfo["id"]) : array();
} else {
    $reports = ControllerReportsCenter::ctrGetAllReports();
}

$catLabels = array();
foreach ($categories as $c) { $catLabels[$c["id"]] = $c["name"]; }
?>

<style>
/* Reports Center Modern Styles */
.rc-modern{background:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);min-height:100vh;padding:1.5rem;}
.rc-glass{background:rgba(255,255,255,0.55);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.3);border-radius:16px;box-shadow:0 8px 32px rgba(31,38,135,0.12);margin-bottom:20px;}
.rc-glass .card-header{background:transparent;border-bottom:1px solid rgba(255,255,255,0.3);border-radius:16px 16px 0 0;padding:16px 20px;}
.rc-glass .card-body{padding:16px 20px;}
.rc-search{border-radius:30px;border:2px solid #e2e8f0;padding:12px 20px 12px 44px;font-size:15px;background:rgba(255,255,255,0.7);transition:all 0.3s ease;width:100%;}
.rc-search:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.15);outline:none;background:#fff;}
.rc-search-wrap{position:relative;}
.rc-search-wrap i{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:16px;}
.rc-cat-list{list-style:none;padding:8px;margin:0;}
.rc-cat-item{margin:2px 0;}
.rc-cat-link{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:10px;color:#475569;text-decoration:none;transition:all 0.2s ease;font-size:14px;font-weight:500;}
.rc-cat-link:hover{background:rgba(59,130,246,0.08);color:#1e40af;transform:translateX(3px);}
.rc-cat-link.active{background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;box-shadow:0 4px 12px rgba(59,130,246,0.3);}
.rc-cat-link i{margin-right:10px;width:18px;text-align:center;font-size:13px;}
.rc-cat-link .badge{font-size:11px;padding:3px 8px;border-radius:20px;font-weight:600;}
.rc-cat-link.active .badge{background:rgba(255,255,255,0.25);color:#fff;}
.rc-cat-header{font-size:10px;text-transform:uppercase;letter-spacing:1.2px;color:#94a3b8;font-weight:700;padding:16px 14px 6px;margin:0;}
.rc-recent-link{display:block;padding:10px 14px;border-radius:10px;color:#475569;text-decoration:none;transition:all 0.2s ease;font-size:13px;}
.rc-recent-link:hover{background:rgba(59,130,246,0.08);color:#1e40af;}
.rc-recent-link i{margin-right:8px;color:#94a3b8;}
.rc-report-row{display:flex;align-items:center;padding:14px 20px;border-bottom:1px solid rgba(0,0,0,0.05);transition:background 0.2s ease;}
.rc-report-row:last-child{border-bottom:none;}
.rc-report-row:hover{background:rgba(59,130,246,0.04);}
.rc-report-name{font-weight:600;color:#1e293b;text-decoration:none;font-size:15px;}
.rc-report-name:hover{color:#2563eb;}
.rc-report-desc{color:#64748b;font-size:13px;margin-top:2px;}
.rc-report-meta{color:#94a3b8;font-size:12px;}
.rc-fav-btn{cursor:pointer;font-size:18px;color:#d1d5db;transition:color 0.2s ease;margin-right:14px;}
.rc-fav-btn:hover,.rc-fav-btn.favorited{color:#f59e0b;}
.rc-empty{text-align:center;padding:60px 20px;color:#94a3b8;}
.rc-empty i{font-size:48px;margin-bottom:16px;display:block;}
.rc-section-title{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;font-weight:700;padding:0 20px 12px;border-bottom:1px solid rgba(0,0,0,0.05);margin-bottom:0;}
</style>

<div class="rc-modern">
    <section class="content-header" style="padding:0;margin-bottom:20px;">
        <h1 style="font-weight:300;font-size:1.8rem;color:#1e293b;"><i class="fa fa-bar-chart" style="color:#3b82f6;"></i> Reports Center</h1>
        <ol class="breadcrumb" style="background:transparent;padding:0;margin:0;font-size:13px;">
            <li><a href="home" style="color:#3b82f6;"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active" style="color:#64748b;">Reports Center</li>
        </ol>
    </section>

    <section class="content" style="padding:0;">
        <!-- Search Bar -->
        <div class="rc-glass" style="margin-bottom:24px;">
            <div class="card-body" style="padding:16px 20px;">
                <form method="GET">
                    <input type="hidden" name="route" value="reports-center">
                    <div class="rc-search-wrap">
                        <i class="fa fa-search"></i>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($searchQ); ?>" class="rc-search" placeholder="Search reports by name, description, or category...">
                        <?php if ($searchQ !== ""): ?>
                            <a href="index.php?route=reports-center" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:14px;"><i class="fa fa-times-circle"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <!-- Left Sidebar: Categories -->
            <div class="col-md-3">
                <div class="rc-glass">
                    <div class="card-header"><h3 class="card-title" style="font-size:14px;font-weight:600;color:#1e293b;margin:0;"><i class="fa fa-th-list" style="color:#3b82f6;"></i> Categories</h3></div>
                    <div class="card-body" style="padding:8px 12px;">
                        <ul class="rc-cat-list">
                            <li class="rc-cat-item">
                                <a href="index.php?route=reports-center" class="rc-cat-link <?php echo (!$activeCat && !$showFavs && $searchQ === "") ? 'active' : ''; ?>">
                                    <span><i class="fa fa-th-large"></i> All Reports</span>
                                    <span class="badge bg-secondary"><?php echo count(ControllerReportsCenter::ctrGetAllReports()); ?></span>
                                </a>
                            </li>
                            <li class="rc-cat-item">
                                <a href="index.php?route=reports-center&favs=1" class="rc-cat-link <?php echo $showFavs ? 'active' : ''; ?>">
                                    <span><i class="fa fa-star"></i> My Favorites</span>
                                    <span class="badge bg-warning"><?php echo count($favIds); ?></span>
                                </a>
                            </li>
                            <li class="rc-cat-header">Report Categories</li>
                            <?php foreach ($categories as $cat): ?>
                                <li class="rc-cat-item">
                                    <a href="index.php?route=reports-center&cat=<?php echo htmlspecialchars($cat["slug"]); ?>" class="rc-cat-link <?php echo ($activeCat === $cat["slug"]) ? 'active' : ''; ?>">
                                        <span><i class="fa <?php echo htmlspecialchars($cat["icon"]); ?>"></i> <?php echo htmlspecialchars($cat["name"]); ?></span>
                                        <span class="badge bg-secondary"><?php echo (int)$cat["report_count"]; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Recently Viewed -->
                <?php if (!empty($recent)): ?>
                <div class="rc-glass">
                    <div class="card-header"><h3 class="card-title" style="font-size:14px;font-weight:600;color:#1e293b;margin:0;"><i class="fa fa-clock-o" style="color:#3b82f6;"></i> Recently Viewed</h3></div>
                    <div class="card-body" style="padding:8px 12px;">
                        <?php foreach ($recent as $r): ?>
                            <a href="index.php?route=<?php echo htmlspecialchars($r["route"]); ?>" class="rc-recent-link" onclick="recordView(<?php echo (int)$r["id"]; ?>)">
                                <i class="fa fa-file-text-o"></i> <?php echo htmlspecialchars($r["name"]); ?>
                                <br><small style="color:#94a3b8;margin-left:26px;"><?php echo date("d M Y", strtotime($r["lastViewed"])); ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Main Content: Report List -->
            <div class="col-md-9">
                <div class="rc-glass">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size:15px;font-weight:600;color:#1e293b;margin:0;">
                            <?php if ($searchQ !== ""): ?>
                                <i class="fa fa-search" style="color:#3b82f6;margin-right:8px;"></i> Search Results for "<?php echo htmlspecialchars($searchQ); ?>"
                            <?php elseif ($showFavs): ?>
                                <i class="fa fa-star" style="color:#f59e0b;margin-right:8px;"></i> My Favorite Reports
                            <?php elseif ($activeCat !== ""): ?>
                                <i class="fa fa-folder-open-o" style="color:#3b82f6;margin-right:8px;"></i> <?php echo htmlspecialchars($catInfo["name"] ?? "Reports"); ?>
                            <?php else: ?>
                                <i class="fa fa-th-large" style="color:#3b82f6;margin-right:8px;"></i> All Reports
                            <?php endif; ?>
                            <span class="badge" style="margin-left:8px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;"><?php echo count($reports); ?></span>
                        </h3>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <?php if (empty($reports)): ?>
                            <div class="text-center" style="padding:40px;">
                                <i class="fa fa-inbox fa-3x" style="color:#cbd5e1;"></i>
                                <p style="color:#94a3b8;margin-top:15px;font-size:14px;">No reports found.</p>
                            </div>
                        <?php else: ?>
                            <table class="table" style="margin-bottom:0;">
                                <thead>
                                    <tr style="background:rgba(59,130,246,0.04);border-bottom:2px solid rgba(59,130,246,0.1);">
                                        <th style="width:40px;padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;font-weight:700;"></th>
                                        <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;font-weight:700;">Report Name</th>
                                        <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;font-weight:700;">Module</th>
                                        <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;font-weight:700;">Last Visited</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $rpt): ?>
                                    <?php
                                        $isFav = in_array((int)$rpt["id"], $favIds, true);
                                        $lastViewed = null;
                                        // Get last viewed for this report
                                        $stmt = Connection::connect()->prepare("SELECT viewedDate FROM report_history WHERE idReport = :rid AND idUser = :uid AND idOrganization = :org ORDER BY viewedDate DESC LIMIT 1");
                                        $org = Tenant::id();
                                        $uid = (int)($_SESSION["idUser"] ?? 0);
                                        $stmt->bindParam(":rid", $rpt["id"], PDO::PARAM_INT);
                                        $stmt->bindParam(":uid", $uid, PDO::PARAM_INT);
                                        $stmt->bindParam(":org", $org, PDO::PARAM_INT);
                                        $stmt->execute();
                                        $histRow = $stmt->fetch();
                                        $lastViewed = $histRow ? $histRow["viewedDate"] : null;
                                    ?>
                                    <tr style="transition:background 0.2s ease;border-bottom:1px solid rgba(0,0,0,0.04);" onmouseover="this.style.background='rgba(59,130,246,0.04)'" onmouseout="this.style.background='transparent'">
                                        <td style="padding:14px 16px;vertical-align:middle;">
                                            <a href="#" class="fav-toggle" data-report="<?php echo (int)$rpt["id"]; ?>" title="Toggle Favorite" style="text-decoration:none;">
                                                <i class="fa <?php echo $isFav ? 'fa-star' : 'fa-star-o'; ?>" style="<?php echo $isFav ? 'color:#f59e0b;' : 'color:#d1d5db;'; ?>font-size:16px;transition:color 0.2s ease;"></i>
                                            </a>
                                        </td>
                                        <td style="padding:14px 16px;vertical-align:middle;">
                                            <a href="index.php?route=<?php echo htmlspecialchars($rpt["route"]); ?>" onclick="recordView(<?php echo (int)$rpt["id"]; ?>)" style="font-weight:600;color:#1e293b;text-decoration:none;font-size:14px;transition:color 0.2s ease;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#1e293b'">
                                                <?php echo htmlspecialchars($rpt["name"]); ?>
                                            </a>
                                            <?php if (!empty($rpt["description"])): ?>
                                                <br><small style="color:#94a3b8;font-size:12px;"><?php echo htmlspecialchars($rpt["description"]); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:14px 16px;vertical-align:middle;">
                                            <span style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;background:rgba(59,130,246,0.08);color:#3b82f6;text-transform:uppercase;letter-spacing:0.5px;"><?php echo htmlspecialchars(ucfirst($rpt["module"])); ?></span>
                                        </td>
                                        <td style="padding:14px 16px;vertical-align:middle;color:#94a3b8;font-size:13px;"><?php echo $lastViewed ? date("d M Y h:i A", strtotime($lastViewed)) : '<span style="color:#cbd5e1;">—</span>'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function recordView(reportId) {
    $.post('index.php', { route: 'reports-center', recordView: 1, reportId: reportId });
}
$(document).on('click', '.fav-toggle', function(e) {
    e.preventDefault();
    var el = $(this);
    var reportId = el.data('report');
    $.post('index.php', { route: 'reports-center', toggleFavorite: 1, reportId: reportId }, function(res) {
        if (res.favorited) {
            el.find('i').removeClass('fa-star-o text-muted').addClass('fa-star text-yellow');
        } else {
            el.find('i').removeClass('fa-star text-yellow').addClass('fa-star-o text-muted');
        }
    }, 'json');
});
</script>