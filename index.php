<?php
// job.php
$title = "求才資訊列表";
include "header.php";

try {
    require_once 'db.php';
    $order = $_POST["order"] ?? "";
    $searchtxt = mysqli_real_escape_string($conn, $_POST["searchtxt"] ?? "");
    $date_start = $_POST["date_start"] ?? "";
    $date_end = $_POST["date_end"] ?? "";

    // 日期區間相反時自動交換
    if ($date_start && $date_end && $date_start > $date_end) {
        [$date_start, $date_end] = [$date_end, $date_start];
    }

    $where = [];
    if ($searchtxt) $where[] = "(company LIKE '%$searchtxt%' OR content LIKE '%$searchtxt%')";
    if ($date_start) $where[] = "pdate >= '$date_start'";
    if ($date_end) $where[] = "pdate <= '$date_end'";

    $sql = "SELECT * FROM job";
    if (count($where) > 0) $sql .= " WHERE " . implode(' AND ', $where);
    if ($order) $sql .= " ORDER BY $order";

    $result = mysqli_query($conn, $sql);
?>
<div class="container position-relative">

    <?php if (!empty($_SESSION['role']) && strtoupper(trim($_SESSION['role'])) === 'M'): ?>
        <br><br>
        <a href="activity_insert.php" class="btn btn-primary position-absolute" style="top: 1rem; right: 1rem;">新增活動</a>
    <?php endif; ?>

    <form action="job.php" method="post">
        <div class="row g-2 align-items-center mb-2">
            <div class="col-auto">
                <select name="order" aria-label="選擇排序欄位" class="form-select">
                    <option selected value="">選擇排序欄位</option>
                    <option value="company" <?=($order=="company")?'selected':''?>>主辦單位</option>
                    <option value="content" <?=($order=="content")?'selected':''?>>活動內容</option>
                    <option value="pdate" <?=($order=="pdate")?'selected':''?>>刊登日期</option>
                </select>
            </div>
            <div class="col-auto">
                <input placeholder="搜尋廠商及內容" value="<?=htmlspecialchars($searchtxt)?>" type="text" name="searchtxt" class="form-control">
            </div>
            <div class="col-auto">
                <input type="date" name="date_start" class="form-control" value="<?=htmlspecialchars($date_start)?>">
            </div>
            <div class="col-auto"><span>~</span></div>
            <div class="col-auto">
                <input type="date" name="date_end" class="form-control" value="<?=htmlspecialchars($date_end)?>">
            </div>
            <div class="col-auto">
                <input class="btn btn-primary" type="submit" value="搜尋">
            </div>
        </div>
    </form>

    <table class="table table-bordered table-striped" id="job_table">
        <thead>
            <tr>
                <th>主辦單位</th>
                <th>活動內容</th>
                <th>刊登日期</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?=htmlspecialchars($row["company"])?></td>
                    <td><?=htmlspecialchars($row["content"])?></td>
                    <td><?=htmlspecialchars($row["pdate"])?></td>
                    <td>
                        <a href="list_update.php?postid=<?=$row["postid"]?>" class="btn btn-primary btn-sm">修改</a>
                        <a href="list_delete.php?postid=<?=$row["postid"]?>" class="btn btn-danger btn-sm">刪除</a>
                        <a href="activity_join.php?postid=<?=$row["postid"]?>" class="btn btn-primary btn-sm">報名</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- 圖表區塊 -->
<div class="mt-4" style="height: 400px;">
    <h3>報名人數統計圖</h3>
    <canvas id="myChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('myChart');
    fetch('chart_data.php')
        .then(response => response.json())
        .then(json => {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: json.labels,
                    datasets: [{
                        label: '# 報名人數',
                        data: json.data,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        barThickness: 40,
                        maxBarThickness: 50
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: '活動名稱',
                                color: '#000',
                                font: { size: 16, weight: 'bold' }
                            },
                            ticks: { autoSkip: false }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: '報名人數',
                                font: { size: 16, weight: 'bold' }
                            },
                            ticks: { precision: 0, stepSize: 1 }
                        }
                    }
                }
            });
        })
        .catch(err => console.error('抓取報名統計資料失敗:', err));
});
</script>

<?php
mysqli_close($conn);
} catch(Exception $e) {
    echo 'Message: '.$e->getMessage();
}
require_once "footer.php";
?>
