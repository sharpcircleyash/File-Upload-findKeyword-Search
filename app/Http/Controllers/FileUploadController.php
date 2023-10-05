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

        $pdfParser = new Parser();
        $pdf = $pdfParser->parseFile($request->file->path());
        $content = $pdf->getText();


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