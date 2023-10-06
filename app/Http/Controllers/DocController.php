<?php

namespace App\Http\Controllers;

use App\Models\Doc;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Foundation\Application;

class DocController extends Controller
{

    /**
     * Home page
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('scandoc');
    }

    /**
     * Scan file and print highlighted search results
     *
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $keyword = $request->input('keyword');
        $text = '';

        if (!empty($keyword)) {
            $record = Doc::orderBy('updated_at', 'desc')->first();
            $fileContents = $record->contents;
            $text = $this->highlightWords($fileContents, $keyword);
        }

        return view('scandoc', compact('text', 'keyword'));

    }

    /**
     * Upload Documents (Word/PDF only)
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:pdf,doc,docx'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }
        $file = $request->file;
        $fileName = $file->getClientOriginalName();

        $contents = $this->getContents($file);

        $count = Doc::all()->count();
        if ($count <= 0) {
            Doc::create([
                'file' => $fileName,
                'extension' => $file->extension(),
                'contents' => $contents
            ]);
        } else {
            Doc::orderBy('id', 'desc')->limit(1)->update([
                'file' => $fileName,
                'extension' => $file->extension(),
                'contents' => $contents
            ]);
        }

        return back()->with('success', 'You have successfully upload file.')->with('file', $fileName);
    }

    /**
     * Highlight Keywords
     *
     * @param $text
     * @param $word
     * @return array|string|string[]|null
     */
    private function highlightWords($text, $word)
    {
        $text = preg_replace('#' . preg_quote($word) . '#i', '<span class="hlw">\\0</span>', $text);
        return $text;
    }

    /**
     * Get File Contents and save in DB
     *
     * @param mixed $file
     * @return string
     * @throws \Exception
     */
    private function getContents(mixed $file)
    {
        $contents = '';
        $extension = $file->extension();

        if ($extension === 'pdf') {
            $pdfParser = new Parser();
            $pdf = $pdfParser->parseFile($file->path());
            $contents = $pdf->getText();
        } elseif (in_array($extension, ['doc', 'docx'])) {
            $phpWord = IOFactory::load($file->path());

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getElements')) {
                        foreach ($element->getElements() as $childElement) {
                            if (method_exists($childElement, 'getText')) {
                                $contents .= $childElement->getText() . ' ';
                            } else if (method_exists($childElement, 'getContent')) {
                                $contents .= $childElement->getContent() . ' ';
                            }
                        }
                    } else if (method_exists($element, 'getText')) {
                        $contents .= $element->getText() . ' ';
                    }
                }
            }
        }

        return $contents;
    }
}
