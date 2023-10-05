<?php
 
namespace App\Http\Controllers;
 
use App\Models\Files;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
 
class FileUploadController extends Controller
{
    public function getFileUploadForm(Request $request)
    {
        $query = $request->get('query');
        if ($request->ajax()) {
            $data = Files::where('content', 'LIKE', '%' . $query . '%')
                ->limit(10)
                ->get();
            $output = '';
            if (count($data) > 0) {
                $output = '<ul class="list-group">';
                foreach ($data as $row) {
                    $output .= '<li class="list-group-item">' . $row->content . '</li>';
                }
                $output .= '</ul>';
            } else {
                $output .= '<li class="list-group-item">' . 'No results' . '</li>';
            }
            return $output;
        }
        
        return view('welcome');
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,docx,doc|max:2048',
        ]);
        


        // Upload File
        $fileName = $request->file->getClientOriginalName();
        $filePath = 'uploads/' . $fileName;
 
        $path = Storage::disk('public')->put($filePath, file_get_contents($request->file));
        $path = Storage::disk('public')->url($path);

        // Check File Extention
        if($request->file->extension() === 'pdf'){
            $pdfParser = new Parser();
            $pdf = $pdfParser->parseFile($request->file->path());
            $content = $pdf->getText();
        }
        elseif($request->file->extension() === 'docx'){
        $striped_content = '';
        $content = '';

        $zip = zip_open($request->file->path());

        if (!$zip || is_numeric($zip)) return false;

        while ($zip_entry = zip_read($zip)) {

            if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

            if (zip_entry_name($zip_entry) != "word/document.xml") continue;

            $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            zip_entry_close($zip_entry);
        }

        zip_close($zip);

        $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
        $content = str_replace('</w:r></w:p>', "\r\n", $content);
        $content = strip_tags($content);
        }
        else{
            if(($fh = fopen($request->file->path(), 'r')) !== false ) 
        {
           $headers = fread($fh, 0xA00);

           // 1 = (ord(n)*1) ; Document has from 0 to 255 characters
           $n1 = ( ord($headers[0x21C]) - 1 );

           // 1 = ((ord(n)-8)*256) ; Document has from 256 to 63743 characters
           $n2 = ( ( ord($headers[0x21D]) - 8 ) * 256 );

           // 1 = ((ord(n)*256)*256) ; Document has from 63744 to 16775423 characters
           $n3 = ( ( ord($headers[0x21E]) * 256 ) * 256 );

           // 1 = (((ord(n)*256)*256)*256) ; Document has from 16775424 to 4294965504 characters
           $n4 = ( ( ( ord($headers[0x21F]) * 256 ) * 256 ) * 256 );

           // Total length of text in the document
           $textLength = ($n1 + $n2 + $n3 + $n4);

           $extracted_plaintext = fread($fh, $textLength);

           $content = $extracted_plaintext;
        }
             
        }
        


        // Insert record
        $insertData_arr = array(
                'name' => $fileName,
                'filepath' => $filePath,
                'content' => $content
        );

        Files::create($insertData_arr);
 
 
        return back()
            ->with('success','File has been successfully uploaded.');
    }
}