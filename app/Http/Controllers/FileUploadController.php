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
            $fileHandle = fopen($request->file->path(), "r");
            $line = @fread($fileHandle, filesize($request->file->path()));   
            $lines = explode(chr(0x0D),$line);
            $outtext = "";
            foreach($lines as $thisline)
              {
                $pos = strpos($thisline, chr(0x00));
                if (($pos !== FALSE)||(strlen($thisline)==0))
                  {
                  } else {
                    $outtext .= $thisline." ";
                  }
              }
             $content = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
        }
        dd($content);
        


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