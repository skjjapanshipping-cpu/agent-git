<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class PriceCalculatorController extends Controller
{
    /**
     * Show the price calculator landing page.
     */
    public function index()
    {
        return view('calculator.index');
    }

    /**
     * Scrape product info from a given URL.
     */
    public function scrapeProduct(Request $request)
    {
        $request->validate(['url' => 'required|url']);

        $url = $request->input('url');
        $result = [
            'success' => false,
            'site' => '',
            'title' => '',
            'price' => 0,
            'shipping' => 0,
            'shipping_text' => '',
            'image' => '',
            'currency' => 'JPY',
        ];

        try {
            // Normalize Mercari URL: remove /en/ to get Japanese version
            if (strpos($url, 'mercari.com') !== false) {
                $url = preg_replace('#/en/(item|search)#', '/$1', $url);
            }

            // Use Googlebot UA for SPA sites (Mercari, PayPay) to get SSR content
            $isSPA = (strpos($url, 'mercari.com') !== false);
            $ua = $isSPA
                ? 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'
                : 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

            $client = new Client([
                'timeout' => 20,
                'headers' => [
                    'User-Agent' => $ua,
                    'Accept-Language' => 'ja,en;q=0.9',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ],
                'verify' => false,
            ]);

            $response = $client->get($url);
            $html = (string) $response->getBody();

            // Detect site
            if (strpos($url, 'auctions.yahoo.co.jp') !== false) {
                $result = $this->parseYahooAuction($html, $result);
            } elseif (strpos($url, 'paypayfleamarket.yahoo.co.jp') !== false || strpos($url, 'paypaymall.yahoo.co.jp') !== false) {
                $result = $this->parsePayPayFlea($html, $result);
            } elseif (strpos($url, 'mercari.com') !== false) {
                $result = $this->parseMercari($html, $result);
            } elseif (strpos($url, 'rakuten.co.jp') !== false) {
                $result = $this->parseRakuten($html, $result);
            } elseif (strpos($url, 'amazon.co.jp') !== false) {
                $result = $this->parseAmazonJP($html, $result);
            } else {
                $result = $this->parseGeneric($html, $result);
            }

            $result['success'] = $result['price'] > 0;

        } catch (\Exception $e) {
            $result['error'] = 'ไม่สามารถดึงข้อมูลจาก URL นี้ได้: ' . $e->getMessage();
        }

        return response()->json($result);
    }

    /**
     * Parse Yahoo Auctions Japan
     */
    private function parseYahooAuction($html, $result)
    {
        $result['site'] = 'Yahoo! Auctions';

        // === Title: og:title first (clean product name) ===
        if (preg_match('/<meta\s+property="og:title"\s+content="([^"]+)"/si', $html, $m)) {
            $result['title'] = trim(preg_replace('/\s*-\s*Yahoo!.*$/u', '', $m[1]));
        }
        // 2) productName from embedded JSON
        if (empty($result['title']) && preg_match('/"productName"\s*:\s*"([^"]+)"/s', $html, $m)) {
            $result['title'] = $m[1];
        }

        // === Price: collect ALL price values and take the highest ===
        // (for auctions: current bid >= starting price, so max = winning bid)
        $allPrices = [];
        if (preg_match_all('/"Price"\s*:\s*"?(\d[\d,]*)"?/s', $html, $matches)) {
            foreach ($matches[1] as $p) {
                $val = (int) str_replace(',', '', $p);
                if ($val > 0) $allPrices[] = $val;
            }
        }
        if (preg_match_all('/"price"\s*:\s*"?(\d[\d,]*)"?/s', $html, $matches)) {
            foreach ($matches[1] as $p) {
                $val = (int) str_replace(',', '', $p);
                if ($val > 0) $allPrices[] = $val;
            }
        }
        if (!empty($allPrices)) {
            $result['price'] = max($allPrices);
        }
        if ($result['price'] == 0 && preg_match('/"currentPrice"\s*:\s*"?(\d[\d,]*)"?/s', $html, $m)) {
            $result['price'] = (int) str_replace(',', '', $m[1]);
        }
        if ($result['price'] == 0 && preg_match('/([\d,]{2,})\s*円/s', $html, $m)) {
            $result['price'] = (int) str_replace(',', '', $m[1]);
        }

        // === Tax: ストア (store) items have 10% consumption tax ===
        // JSON "Price"/"price" is tax-excluded for store items
        if ($result['price'] > 0) {
            $taxApplied = false;
            // Method 1: Find "Tax" amount in JSON (e.g. "Tax":435)
            if (preg_match('/"Tax"\s*:\s*"?(\d+)"?/s', $html, $taxM)) {
                $taxVal = (int) $taxM[1];
                if ($taxVal > 0 && $taxVal < $result['price']) {
                    $result['price'] += $taxVal;
                    $taxApplied = true;
                }
            }
            // Method 2: Detect store item → apply 10% tax
            if (!$taxApplied && (
                strpos($html, 'ストアの情報') !== false ||
                preg_match('/"storeName"\s*:/si', $html) ||
                preg_match('/"isStore"\s*:\s*true/si', $html) ||
                preg_match('/"sellerType"\s*:\s*"store"/si', $html)
            )) {
                $result['price'] = $result['price'] + (int) floor($result['price'] * 0.1);
            }
        }

        // === Shipping: from HTML (often loaded client-side, may not be available) ===
        if (preg_match('/送料\s*[：:]?\s*([\d,]+)\s*円/su', $html, $m)) {
            $result['shipping'] = (int) str_replace(',', '', $m[1]);
            $result['shipping_text'] = number_format($result['shipping']) . ' เยน';
        } elseif (preg_match('/送料無料|送料込み/s', $html)) {
            $result['shipping'] = 0;
            $result['shipping_text'] = 'ส่งฟรี (送料込み)';
        } elseif (preg_match('/着払い/s', $html)) {
            $result['shipping_text'] = 'ผู้รับชำระค่าส่ง (着払い)';
        }
        // Note: Yahoo Auctions shipping is usually loaded via client-side JS — may not be found

        // === Image ===
        if (preg_match('/<meta\s+property="og:image"\s+content="([^"]+)"/si', $html, $m)) {
            $result['image'] = $m[1];
        }

        return $result;
    }

    /**
     * Parse PayPay Flea Market
     */
    private function parsePayPayFlea($html, $result)
    {
        $result['site'] = 'PayPay フリマ';

        // Title
        if (preg_match('/<meta\s+property="og:title"\s+content="([^"]+)"/si', $html, $m)) {
            $result['title'] = trim($m[1]);
        } elseif (preg_match('/<title>(.*?)<\/title>/s', $html, $m)) {
            $result['title'] = trim(strip_tags($m[1]));
        }

        // JSON-LD
        if (preg_match_all('/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $html, $jsonMatches)) {
            foreach ($jsonMatches[1] as $jsonStr) {
                $json = json_decode($jsonStr, true);
                if (!$json) continue;
                if (isset($json['offers']['price']) && $result['price'] == 0) {
                    $result['price'] = (int) $json['offers']['price'];
                }
                if (isset($json['name']) && empty($result['title'])) {
                    $result['title'] = $json['name'];
                }
                if (isset($json['image']) && empty($result['image'])) {
                    $result['image'] = is_array($json['image']) ? $json['image'][0] : $json['image'];
                }
            }
        }

        // Price from embedded JSON
        if ($result['price'] == 0 && preg_match('/"price"\s*:\s*"?(\d[\d,]*)"?/s', $html, $m)) {
            $result['price'] = (int) str_replace(',', '', $m[1]);
        }
        // Price from HTML
        if ($result['price'] == 0 && preg_match('/([\d,]+)\s*円/s', $html, $m)) {
            $result['price'] = (int) str_replace(',', '', $m[1]);
        }

        // Shipping — PayPay usually includes shipping
        if (preg_match('/送料無料|送料込み/s', $html)) {
            $result['shipping'] = 0;
            $result['shipping_text'] = 'ส่งฟรี (送料込み)';
        }

        // Image
        if (empty($result['image']) && preg_match('/<meta\s+property="og:image"\s+content="([^"]+)"/si', $html, $m)) {
            $result['image'] = $m[1];
        }

        return $result;
    }

    /**
     * Parse Mercari
     */
    private function parseMercari($html, $result)
    {
        $result['site'] = 'Mercari';

        if (preg_match('/<title>(.*?)<\/title>/s', $html, $m)) {
            $result['title'] = trim(strip_tags($m[1]));
        }

        // JSON-LD or meta
        if (preg_match('/"price"\s*:\s*"?(\d[\d,]*)"?/s', $html, $m)) {
            $result['price'] = (int) str_replace(',', '', $m[1]);
        } elseif (preg_match('/([\d,]+)\s*円/s', $html, $m)) {
            $result['price'] = (int) str_replace(',', '', $m[1]);
        }

        // Mercari shipping is usually included
        $result['shipping'] = 0;
        $result['shipping_text'] = 'ส่งฟรี (送料込み)';

        if (preg_match('/og:image["\s]*content="([^"]+)"/s', $html, $m)) {
            $result['image'] = $m[1];
        }

        return $result;
    }

    /**
     * Parse Rakuten
     */
    private function parseRakuten($html, $result)
    {
        $result['site'] = 'Rakuten';

        if (preg_match('/<title>(.*?)<\/title>/s', $html, $m)) {
            $result['title'] = trim(strip_tags($m[1]));
        }

        if (preg_match('/"price"\s*:\s*"?(\d[\d,]*)"?/s', $html, $m)) {
            $result['price'] = (int) str_replace(',', '', $m[1]);
        } elseif (preg_match('/([\d,]+)\s*円/s', $html, $m)) {
            $result['price'] = (int) str_replace(',', '', $m[1]);
        }

        if (preg_match('/送料無料/s', $html)) {
            $result['shipping'] = 0;
            $result['shipping_text'] = 'ส่งฟรี';
        } elseif (preg_match('/送料\s*([\d,]+)\s*円/s', $html, $m)) {
            $result['shipping'] = (int) str_replace(',', '', $m[1]);
            $result['shipping_text'] = number_format($result['shipping']) . ' เยน';
        }

        if (preg_match('/og:image["\s]*content="([^"]+)"/s', $html, $m)) {
            $result['image'] = $m[1];
        }

        return $result;
    }

    /**
     * Parse Amazon JP
     */
    private function parseAmazonJP($html, $result)
    {
        $result['site'] = 'Amazon Japan';

        if (preg_match('/<title>(.*?)<\/title>/s', $html, $m)) {
            $result['title'] = trim(strip_tags($m[1]));
        }

        if (preg_match('/￥\s*([\d,]+)/s', $html, $m)) {
            $result['price'] = (int) str_replace(',', '', $m[1]);
        }

        if (preg_match('/og:image["\s]*content="([^"]+)"/s', $html, $m)) {
            $result['image'] = $m[1];
        }

        return $result;
    }

    /**
     * Parse generic Japanese e-commerce page
     */
    private function parseGeneric($html, $result)
    {
        $result['site'] = 'Other';

        if (preg_match('/<title>(.*?)<\/title>/s', $html, $m)) {
            $result['title'] = trim(strip_tags($m[1]));
        }

        // Try JSON-LD price
        if (preg_match('/"price"\s*:\s*"?(\d[\d,]*)"?/s', $html, $m)) {
            $result['price'] = (int) str_replace(',', '', $m[1]);
        } elseif (preg_match('/([\d,]+)\s*円/s', $html, $m)) {
            $result['price'] = (int) str_replace(',', '', $m[1]);
        }

        if (preg_match('/og:image["\s]*content="([^"]+)"/s', $html, $m)) {
            $result['image'] = $m[1];
        }

        return $result;
    }
}
