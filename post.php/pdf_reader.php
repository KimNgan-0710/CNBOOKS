<?php
// Đảm bảo đã cài đặt thư viện smalot/pdfparser qua Composer
require_once __DIR__ . '/../vendor/autoload.php';

use Smalot\PdfParser\Parser;

class PDFReader {
    private $parser;
    private $pdfDirectory;
    
    public function __construct($pdfDirectory = '../pdf_books/') {
        $this->parser = new Parser();
        $this->pdfDirectory = $pdfDirectory;
    }
    
    /**
     * Lấy danh sách tất cả các file PDF trong thư mục
     * @return array Mảng chứa thông tin về các file PDF
     */
    public function getAllPDFBooks() {
        $books = [];
        $files = glob($this->pdfDirectory . '*.pdf');
        
        foreach ($files as $file) {
            $bookInfo = $this->getBookInfo($file);
            if ($bookInfo) {
                $books[] = $bookInfo;
            }
        }
        
        return $books;
    }
    
    /**
     * Lấy thông tin cơ bản về một file PDF
     * @param string $filePath Đường dẫn đến file PDF
     * @return array|null Thông tin về sách hoặc null nếu có lỗi
     */
    public function getBookInfo($filePath) {
        try {
            $pdf = $this->parser->parseFile($filePath);
            
            // Lấy metadata từ PDF
            $details = $pdf->getDetails();
            
            // Lấy nội dung trang đầu tiên để trích xuất thông tin
            $text = $pdf->getText();
            $firstPageText = $this->getFirstPageText($pdf);
            
            // Tạo slug từ tên file để sử dụng trong URL
            $fileName = basename($filePath);
            $slug = pathinfo($fileName, PATHINFO_FILENAME);
            
            return [
                'title' => isset($details['Title']) ? $details['Title'] : $this->extractTitle($firstPageText, $fileName),
                'author' => isset($details['Author']) ? $details['Author'] : 'Không xác định',
                'subject' => isset($details['Subject']) ? $details['Subject'] : '',
                'pages' => count($pdf->getPages()),
                'file_name' => $fileName,
                'file_path' => $filePath,
                'slug' => $slug,
                'preview_text' => $this->getPreviewText($firstPageText),
                'toc' => $this->extractTableOfContents($text)
            ];
        } catch (Exception $e) {
            error_log('Lỗi khi đọc file PDF: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Lấy nội dung chi tiết của một file PDF
     * @param string $slug Slug của sách cần lấy
     * @return array|null Thông tin chi tiết về sách hoặc null nếu không tìm thấy
     */
    public function getBookBySlug($slug) {
        $files = glob($this->pdfDirectory . '*.pdf');
        
        foreach ($files as $file) {
            $fileName = basename($file);
            $fileSlug = pathinfo($fileName, PATHINFO_FILENAME);
            
            if ($fileSlug === $slug) {
                $bookInfo = $this->getBookInfo($file);
                if ($bookInfo) {
                    // Thêm nội dung chi tiết
                    $pdf = $this->parser->parseFile($file);
                    $bookInfo['content'] = $this->extractContent($pdf);
                    return $bookInfo;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Trích xuất tiêu đề từ nội dung trang đầu tiên nếu metadata không có
     */
    private function extractTitle($text, $fileName) {
        // Tìm dòng đầu tiên có ý nghĩa trong văn bản
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) > 10 && strlen($line) < 100) {
                return $line;
            }
        }
        
        // Nếu không tìm thấy, sử dụng tên file
        return str_replace('_', ' ', pathinfo($fileName, PATHINFO_FILENAME));
    }
    
    /**
     * Lấy văn bản trang đầu tiên
     */
    private function getFirstPageText($pdf) {
        $pages = $pdf->getPages();
        if (count($pages) > 0) {
            return $pages[0]->getText();
        }
        return '';
    }
    
    /**
     * Lấy đoạn văn bản ngắn để hiển thị xem trước
     */
    private function getPreviewText($text) {
        $text = preg_replace('/\s+/', ' ', $text);
        $preview = substr($text, 0, 300);
        return $preview . (strlen($text) > 300 ? '...' : '');
    }
    
    /**
     * Trích xuất mục lục từ nội dung PDF
     */
    private function extractTableOfContents($text) {
        $toc = [];
        
        // Tìm kiếm các dòng có thể là mục lục
        // Đây là một cách đơn giản, có thể cần điều chỉnh tùy theo định dạng PDF
        $pattern = '/(?:Chương|Phần|Mục|Chapter|Section)\s+\d+[.:]\s*(.*?)(?=\d+|\n|$)/i';
        if (preg_match_all($pattern, $text, $matches)) {
            foreach ($matches[0] as $index => $match) {
                $toc[] = trim($match);
            }
        }
        
        // Nếu không tìm thấy mục lục theo cách trên, thử cách khác
        if (empty($toc)) {
            $lines = explode("\n", $text);
            $inToc = false;
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                // Kiểm tra xem đây có phải là tiêu đề mục lục
                if (preg_match('/(Mục lục|Nội dung|Table of Contents|Contents)/i', $line)) {
                    $inToc = true;
                    continue;
                }
                
                // Nếu đang ở trong phần mục lục và dòng có số trang
                if ($inToc && preg_match('/^(.+)\s+\d+$/', $line, $matches)) {
                    $toc[] = trim($matches[1]);
                }
                
                // Dừng khi gặp một tiêu đề lớn khác
                if ($inToc && preg_match('/^(Chương|Chapter|Phần|Part)\s+\d+/i', $line)) {
                    $inToc = false;
                }
            }
        }
        
        return $toc;
    }
    
    /**
     * Trích xuất nội dung từ PDF theo từng trang
     */
    private function extractContent($pdf) {
        $content = [];
        $pages = $pdf->getPages();
        
        foreach ($pages as $pageNumber => $page) {
            $content[] = [
                'page_number' => $pageNumber + 1,
                'text' => $page->getText()
            ];
        }
        
        return $content;
    }
    
    /**
     * Trích xuất toàn bộ text từ file PDF
     */
    public function extractText($filePath) {
        $pdf = $this->parser->parseFile($filePath);
        return $pdf->getText();
    }
}
?>