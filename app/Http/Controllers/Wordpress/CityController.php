<?php

namespace App\Http\Controllers\Wordpress;

use App\Http\Resources\Wordpress\CityResource;
use App\Models\Wordpress\OptionWordpress;
use App\Models\Wordpress\TermWordpress;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CityController extends Controller
{
    protected $term,$option;

    public function __construct(TermWordpress $term,OptionWordpress $option)
    {
        $this->term = $term;
        $this->option = $option;
    }

    public function index(Request $request)
    {
        $lang='en';
        if (isset($request->lang)) {
            $lang=$request->lang;
        }
        $term = $this->term->leftjoin('icl_translations','terms.term_id','icl_translations.element_id')
            ->where('icl_translations.language_code',$lang)
            ->where('icl_translations.element_type','tax_property_city')
            ->leftjoin('term_taxonomy', 'terms.term_id', 'term_taxonomy.term_id')
            ->where('term_taxonomy.taxonomy', 'property_city')
            ->select('terms.name', 'term_taxonomy.count', 'term_taxonomy.term_id as term_taxonomy_id')
            ->get();
        foreach ($term as $taxonomy_id) {
            $taxonomy_id->option = $this->option->get('taxonomy_' . $taxonomy_id->term_taxonomy_id);
        }
        return response()->json(['status' => 1, 'data' =>  CityResource::collection($term), 'message' => 'Message_Done']);
    }
}
