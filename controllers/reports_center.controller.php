<?php
require_once __DIR__ . '/../models/reports_center.model.php';

/**
 * Reports Center Controller
 */
class ControllerReportsCenter {

    public static function ctrGetCategories() {
        return ReportsCenterModel::mdlGetCategories();
    }

    public static function ctrGetReportsByCategory($categoryId) {
        return ReportsCenterModel::mdlGetReportsByCategory($categoryId);
    }

    public static function ctrSearchReports($query) {
        return ReportsCenterModel::mdlSearchReports($query);
    }

    public static function ctrGetAllReports() {
        return ReportsCenterModel::mdlGetAllReports();
    }

    public static function ctrGetReportBySlug($slug) {
        return ReportsCenterModel::mdlGetReportBySlug($slug);
    }

    public static function ctrToggleFavorite($reportId) {
        $userId = (int)($_SESSION["idUser"] ?? 0);
        if ($userId == 0) return false;
        return ReportsCenterModel::mdlToggleFavorite($userId, $reportId);
    }

    public static function ctrRecordView($reportId) {
        $userId = (int)($_SESSION["idUser"] ?? 0);
        if ($userId > 0) {
            ReportsCenterModel::mdlRecordView($userId, $reportId);
        }
    }

    public static function ctrGetFavoriteIds() {
        $userId = (int)($_SESSION["idUser"] ?? 0);
        if ($userId == 0) return array();
        return ReportsCenterModel::mdlGetFavoriteIds($userId);
    }

    public static function ctrGetRecentReports($limit = 10) {
        $userId = (int)($_SESSION["idUser"] ?? 0);
        if ($userId == 0) return array();
        return ReportsCenterModel::mdlGetRecentReports($userId, $limit);
    }

    public static function ctrSeedReports() {
        ReportsCenterModel::mdlSeedReports();
    }
}