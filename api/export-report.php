<?php
/**
 * Export Report API
 * Handles CSV export requests
 * LACOWE Welfare MIS
 */

require_once '../config/config.php';
require_once '../includes/Auth.php';
require_once '../includes/ReportGenerator.php';

Auth::requireAuth();
Auth::requireRole([1, 2, 3]); // Admin roles only

$type = $_GET['type'] ?? '';
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

$reportGen = new ReportGenerator();

switch ($type) {
    case 'members':
        $headers = ['Member #', 'First Name', 'Last Name', 'ID Number', 'Phone', 'Email', 'Status', 'Joined Date', 'Total Savings (KES)'];
        $data = $reportGen->getMemberReport();
        $filename = "Members_Report_" . date('Ymd') . ".csv";
        $reportGen->arrayToCsv($headers, $data, $filename);
        break;

    case 'loans':
        $headers = ['Loan #', 'Member #', 'Member Name', 'Type', 'Principal', 'Rate (%)', 'Total Due', 'Paid', 'Outstanding', 'Status', 'Disbursed', 'Maturity'];
        $data = $reportGen->getLoanReport($startDate, $endDate);
        $filename = "Loans_Report_" . date('Ymd') . ".csv";
        $reportGen->arrayToCsv($headers, $data, $filename);
        break;

    case 'transactions':
        $headers = ['Date', 'Reference', 'Member #', 'Member Name', 'Account #', 'Account Type', 'Trans Type', 'Amount', 'Status'];
        $data = $reportGen->getTransactionReport($startDate, $endDate);
        $filename = "Transactions_Report_" . date('Ymd') . ".csv";
        $reportGen->arrayToCsv($headers, $data, $filename);
        break;

    case 'bank_accounts':
        $headers = ['Member #', 'Member Name', 'Bank Name', 'Account Name', 'Account Number', 'Branch', 'Status'];
        $data = $reportGen->getBankAccountReport();
        $filename = "Bank_Accounts_Report_" . date('Ymd') . ".csv";
        $reportGen->arrayToCsv($headers, $data, $filename);
        break;

    default:
        die("Invalid report type specified.");
}
