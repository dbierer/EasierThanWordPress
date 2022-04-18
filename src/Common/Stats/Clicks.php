<?php
namespace FileCMS\Common\Stats;

use SplFileObject;
use Throwable;
class Clicks
{
    const HOME = '/home';
    /**
     * Records page counts by url, year, month day
     * Doesn't record clicks that match self::IGNORE_URLS
     *
     * @param string $url      : URL to record
     * @param string $click_fn : file name of CSV file containing clicks
     * @return boolean TRUE if OK; FALSE otherwise
     */
    public static function add(string $url, string $click_fn) : bool
    {
        $ok = FALSE;
        if ($url === '/' || $url === self::HOME) return TRUE;
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $obj = new SplFileObject($click_fn, 'a');
            $ok = (bool) $obj->fputcsv([$url, date('Y-m-d'), date('H:i:s'), $ip, 1]);
            unset($obj);
        } catch (Throwable $t) {
            error_log(__METHOD__ . ':' . $t->getMessage() . ':' . $t->getTraceAsString());
        }
        return $ok;
    }
    /**
     * Returns array sorted by URL + date w/ totals
     *
     * @param string $click_fn : file name of CSV file containing clicks
     * @return array $clicks : sorted by URL + date w/ totals
     */
    public static function get(string $click_fn) : array
    {
        $clicks = [];
        if (!file_exists($click_fn)) return $clicks;
        try {
            $obj = new SplFileObject($click_fn, 'r');
            while ($row = $obj->fgetcsv()) {
                if (empty($row[0]) || empty($row[1])) continue;
                $key = $row[0] . '-' . $row[1];
                if (empty($clicks[$key])) {
                    $clicks[$key] = [
                        'url' => $row[0],
                        'date' => $row[1],
                        'time' => $row[2],
                        'hits' => 1
                    ];
                } else {
                    $clicks[$key]['hits']++;
                }
            }
            asort($clicks);
            unset($obj);
        } catch (Throwable $t) {
            error_log(__METHOD__ . ':' . $t->getMessage() . ':' . $t->getTraceAsString());
        }
        return $clicks;
    }
}
