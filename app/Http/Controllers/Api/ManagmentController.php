<?php



namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;

use Illuminate\Http\Request;



use JWTAuth;

use App\User;

use App\Speciality;

use App\SubLevel;

use App\Level;

use App\Section;

use App\Module;

use App\Subject;

use Tymon\JWTAuth\Exceptions\JWTException;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Arr;



use Jenssegers\Agent\Agent;



use Crypt;

use Hash;

use Mail;

use Carbon\Carbon;

use Illuminate\Support\Facades\Storage;



use Illuminate\Foundation\Auth\VerifiesEmails;

use Illuminate\Auth\Events\Verified;





use Illuminate\Support\Facades\Validator;



class ManagmentController extends Controller

{

    public function Register_spec_by_sublevel_code(Request $request){



        $messages = [

            'sub_level_id.required' => 'يجب عليك إختيار مستواك الدراسي',

        ];

    

    $validator = Validator::make($request->all(), [

       // 'name' => ['string', 'max:255'],

        'sub_level_id'=>'required',

    ], $messages);



    /*********************** */

    if($validator->fails()){

        $response = array($validator->messages());

        $response = $response[0]->first();

   //echo  $test;

        //return response()->json($response, 400);

        return response()->json(['status'=>false ,'message'=> $response],400);

      }

  

      /******************** */

      



        $user = User::where('id',Auth::user()->id)->first();



       // $subLevel = SubLevel::where('code',$request->code_level)->with('specialities')->first();



        $spec = Speciality::where('sub_level_id',$request->sub_level_id)->first();



        $user->specialities_id = $spec->id;

        $user->save();

        

        return response()->json(['status'=>true,'spec_id' => $spec->id],200);

     

     }





     /***************************

      *

      *getAllSpcs

      *

      */



      public function getAllSpcs(Request $request){



        $messages = [

            'sub_level_id.required' => 'يجب عليك إختيار مستواك الدراسي',

        ];

    

        $validator = Validator::make($request->all(), [

          // 'name' => ['string', 'max:255'],

          'sub_level_id'=>'required',

    

        ], $messages);



       /*********************** */

        if($validator->fails()){

             $response = array($validator->messages());

             $response = $response[0]->first();

   //echo  $test;

        //return response()->json($response, 400);

             return response()->json(['status'=>false ,'message'=> $response],400);

         }





      //  $subLevel = SubLevel::where('code',$request->code_level)->first();



       

        $spec = Speciality::where('sub_level_id',$request->sub_level_id)->get();



        return response()->json($spec,200);

     

     }





     public function getAllLevels(Request $request){



        $levels = Level::with('subLevels')->get();



        foreach($levels as $level){



            $level['count_sublevel'] = $level->subLevels->count();



            foreach($level->subLevels as $sublevel){



                if(Speciality::where('sub_level_id',$sublevel->id)->where('hidden',0)->first()){

                    $sublevel['has_spcs'] = true;

                }else{

                    $sublevel['has_spcs'] = false;

                }



                if($sublevel->hidden == 1){

                    $level['count_sublevel'] = 0;

                    $level['sub_level_id'] = $sublevel->id;

                }



            }

            

        }







        return response()->json($levels->sortByDesc('count_sublevel')->values(),200);

     

     }

     /**********

      * **

      **

      *

      **

      *

      *

      *

      */





     public function getSectionsBySpc(Request $request){



        $messages = [

            'spec_id.required' => 'يجب عليك إختيار مستواك الدراسي',

   

        ];

    

        $validator = Validator::make($request->all(), [

          // 'name' => ['string', 'max:255'],

          'spec_id'=>'required',

    

        ], $messages);



       /*********************** */

        if($validator->fails()){

             $response = array($validator->messages());

             $response = $response[0]->first();

   //echo  $test;

        //return response()->json($response, 400);

             return response()->json(['status'=>false ,'message'=> $response],400);

         }





       // $subLevel = SubLevel::where('code',$request->code_level)->first();



        $spec_section = Section::where('specialities_id',$request->spec_id)->count();



        $spec_section_all = collect();

        $file_contain = collect();



        if($spec_section == 1){



            $spec_section = Section::where('specialities_id',$request->spec_id)->first();



            if($spec_section->hidden == 1){



                $file_contain['has_sections']= false;

                $file_contain['empty']= false;

                $file_contain['modules']= Module::where('section_id',$spec_section->id)->with('subSections')->get();

                



                $spec_section_all->push($file_contain);



                return response()->json($spec_section_all[0],200);

          

            }elseif($spec_section->hidden == 0){





                $spec_section_list = Section::where('specialities_id',$request->spec_id)->get();





                $file_contain['has_sections']= true;

                $file_contain['empty']= false;

                $file_contain['sections']= $spec_section_list;

                



                $spec_section_all->push($file_contain);



                return response()->json($spec_section_all[0],200);



            }



        }elseif($spec_section > 1){



           

            $file_contain['has_sections']= true;

            $file_contain['empty']= false;

            $file_contain['sections']=  Section::where('specialities_id',$request->spec_id)->get();

           

            $spec_section_all->push($file_contain);

            

            

            return response()->json($spec_section_all[0]);

       

       

        }elseif($spec_section == 0){



            return response()->json(['has_sections' => false ,'empty' => true],200);

        }



       

      //  $spec = Speciality::where('sub_level_id',$subLevel->id)->get();     

     }

     





     /***************************

      *

      *getAllSpcs

      *

      */



      public function  get_Modules_Or_Sections_by_sublevel_code(Request $request){



        $messages = [

            'sub_level_id.required' => 'يجب عليك إختيار مستواك الدراسي',

        ];

    

        $validator = Validator::make($request->all(), [

          // 'name' => ['string', 'max:255'],

          'sub_level_id'=>'required',

        ], $messages);



       /*********************** */

        if($validator->fails()){

             $response = array($validator->messages());

             $response = $response[0]->first();

   //echo  $test;

        //return response()->json($response, 400);

             return response()->json(['status'=>false ,'message'=> $response],400);

         }





       // $subLevel = SubLevel::where('code',$request->code_level)->first();



       // $subLevel = SubLevel::where('code',$request->code_level)->first();



        $spec = Speciality::where('sub_level_id',$request->sub_level_id)->first();



        $spec_section = Section::where('specialities_id',$spec->id)->count();



        $spec_section_all = collect();

        $file_contain = collect();



        if($spec_section == 1){



            $spec_section = Section::where('specialities_id',$spec->id)->first();



            if($spec_section->hidden == 1){



                $file_contain['has_sections']= false;

                $file_contain['empty']= false;

                $modules = Module::where('section_id',$spec_section->id)->with('subSections.second_subsection')->get();
                
                foreach($modules as $module){

                     if($module->subSections->isNotEmpty()){

                        foreach($module->subSections as $subSection){

                       
                            if($subSection->second_subsection->isNotEmpty()){

                                
                                if($subSection->second_subsection[0]->hidden == 1){

                                    $subSection['has_second_sub_section'] =  0;
                                    $subSection['second_sub_section_id'] =  $subSection->second_subsection[0]->id;

                                 }elseif($subSection->second_subsection[0]->hidden == 0){

                                    $subSection['has_second_sub_section']  =  1;

                                 }

                             }else{
                                $subSection['has_second_sub_section'] =  0;
                             }
                            
                        }

                     }

                }



                $file_contain['modules']= $modules;


                $spec_section_all->push($file_contain);



                return response()->json($spec_section_all[0],200);

          

            }elseif($spec_section->hidden == 0){



                $spec_section_list = Section::where('specialities_id',$request->spec_id)->get();



                $file_contain['has_sections']= true;

                $file_contain['empty']= false;

                $file_contain['sections']= $spec_section_list;



                $spec_section_all->push($file_contain);



                return response()->json($spec_section_all[0],200);



            }



        }elseif($spec_section > 1){



           

            $file_contain['has_sections']= true;

            $file_contain['empty']= false;

            $file_contain['sections']=  Section::where('specialities_id',$spec->id)->get();

           

            $spec_section_all->push($file_contain);

            

            

            return response()->json($spec_section_all[0]);

       

       

        }elseif($spec_section == 0){



            return response()->json(['has_sections' => false ,'empty' => true],200);

        }



       

      //  $spec = Speciality::where('sub_level_id',$subLevel->id)->get();     

     }

     



     /**********************

      * 













      */



      public function  get_spec_id_by_sublevel_code(Request $request){



        $messages = [

            'sub_level_id.required' => 'يجب عليك إختيار مستواك الدراسي',

        ];

    

        $validator = Validator::make($request->all(), [

          // 'name' => ['string', 'max:255'],

          'sub_level_id'=>'required',

        ], $messages);



       /*********************** */

        if($validator->fails()){

             $response = array($validator->messages());

             $response = $response[0]->first();

   //echo  $test;

        //return response()->json($response, 400);

             return response()->json(['status'=>false ,'message'=> $response],400);

         }





       // $subLevel = SubLevel::where('code',$request->code_level)->first();



       // $subLevel = SubLevel::where('code',$request->code_level)->first();



        $spec = Speciality::where('sub_level_id',$request->sub_level_id)->first();



        return response()->json(['status'=>true ,'spec_id' => $spec->id],200);



      }



      /*********

       * 

       * 

       * 

       * 

       */



      public function search_by_sublevel_code(Request $request){





        $messages = [

            'sub_level_id.required' => 'يجب عليك إختيار مستواك الدراسي',

            'name.required' => 'يجب عليك ادخال اسم للبحث عنه',

        ];

    

        $validator = Validator::make($request->all(), [

          // 'name' => ['string', 'max:255'],

          'sub_level_id'=>'required',

          'name'=>'required',

        ], $messages);



       /*********************** */

        if($validator->fails()){

             $response = array($validator->messages());

             $response = $response[0]->first();

   //echo  $test;

        //return response()->json($response, 400);

             return response()->json(['status'=>false ,'message'=> $response],400);

         }





       // $subLevel = SubLevel::where('code',$request->code_level)->first();



       

        $spec = Speciality::where('sub_level_id',$request->sub_level_id)->where('name', 'LIKE', '%' . $request->name . '%')->get();



        return response()->json($spec,200);

     

     }





/******

 * 

 * 

 * 

 * 

 * 

 * 

 * 

 * 

 * 

 * 

 */

     public function search_by_models(Request $request){





        $messages = [

            'spec_id.required' => 'يجب عليك إختيار القسم المراد البحث فيه',

            'name.required' => 'يجب عليك ادخال اسم للبحث عنه',

        ];

    

        $validator = Validator::make($request->all(), [

          // 'name' => ['string', 'max:255'],

          'spec_id'=>'required',

          'name'=>'required',

        ], $messages);



       /*********************** */

        if($validator->fails()){

             $response = array($validator->messages());

             $response = $response[0]->first();

   //echo  $test;

        //return response()->json($response, 400);

             return response()->json(['status'=>false ,'message'=> $response],400);

         }



        $modul = collect();



        $spec_sections = Section::where('specialities_id',$request->spec_id)->get();

    





        foreach($spec_sections as $spec_section){



            $modules = Module::where('section_id',$spec_section->id)->where('name', 'LIKE', '%' . $request->name . '%')->with('subSections.second_subsection')->get();

                 


            foreach($modules as $module){

                if($module->subSections->isNotEmpty()){

                   foreach($module->subSections as $subSection){

                  
                       if($subSection->second_subsection->isNotEmpty()){

                           
                           if($subSection->second_subsection[0]->hidden == 1){

                               $subSection['has_second_sub_section'] =  0;
                               $subSection['second_sub_section_id'] =  $subSection->second_subsection[0]->id;

                            }elseif($subSection->second_subsection[0]->hidden == 0){

                               $subSection['has_second_sub_section']  =  1;

                            }

                        }else{
                           $subSection['has_second_sub_section'] =  0;
                        }
                       
                   }

                }

           }


           
         foreach($modules as $module){



            if($module->subSections->isNotEmpty()){



                $modul->push($module);



            }



        }

     //  $subjects = $subjects->whereNotNull('contents');

            



       }



 //  section->modules->subsection->subject;



   

//  $subjects = $subjects->whereNotNull('contents');





return response()->json($modul,200);

       

     

     }







     public function search_for_subjects(Request $request){





        $messages = [

            'spec_id.required' => 'يجب عليك إختيار القسم الفرعي المراد البحث فيه',

            'name.required' => 'يجب عليك ادخال اسم للبحث عنه',

        ];

    

        $validator = Validator::make($request->all(), [

          // 'name' => ['string', 'max:255'],

          'spec_id'=>'required',

          'name'=>'required',

        ], $messages);



       /*********************** */

        if($validator->fails()){

             $response = array($validator->messages());

             $response = $response[0]->first();

   //echo  $test;

        //return response()->json($response, 400);

             return response()->json(['status'=>false ,'message'=> $response],400);

         }



        $sub= collect();





        $spec_sections = Section::where('specialities_id',$request->spec_id)->with('modules.subSections.second_subsection')->get();

    



             foreach($spec_sections as $spec_section){



                if($spec_section->modules->isNotEmpty()){



                    foreach($spec_section->modules as $module){



                        if($module->subSections->isNotEmpty()){



                            foreach($module->subSections as $subsection){



                                if($subsection->second_subsection->isNotEmpty()){


                                    foreach($subsection->second_subsection as $Secondsubsection){


                                        $subjects =  Subject::where('second_sub_id',$Secondsubsection->id)->where('name', 'LIKE', '%' . $request->name . '%')->with('contents')->get();

                 

                                        foreach($subjects as $subject){
     
     
     
                                          if($subject->contents->isNotEmpty()){
     
                                     
     
                                              $sub->push($subject);
     
                                          
     
                                             }
     
     
     
                                     }

                                    }

                                   


                                }
                                



                            }



                        }

                       



                    }

                    

                }



            }



      //  section->modules->subsection->subject;



        

     //  $subjects = $subjects->whereNotNull('contents');

            return response()->json($sub,200);

     

     }

     /************

      * 

 





      */





      public function search_subjects_by_subsection(Request $request){





        $messages = [

            'second_sub_id.required' => 'يجب عليك إختيار القسم الفرعي المراد البحث فيه',

            'name.required' => 'يجب عليك ادخال اسم للبحث عنه',

        ];

    

        $validator = Validator::make($request->all(), [

          // 'name' => ['string', 'max:255'],

          'second_sub_id'=>'required',

          'name'=>'required',

        ], $messages);



       /*********************** */

       if($validator->fails()){

        $response = array($validator->messages());

        $response = $response[0]->first();

//echo  $test;

   //return response()->json($response, 400);

        return response()->json(['status'=>false ,'message'=> $response],400);

       }





   $sub= collect();

   

 $subjects =  Subject::where('second_sub_id',$request->second_sub_id)->where('name', 'LIKE', '%' . $request->name . '%')->with('contents')->Has('contents')->get();

             

 return response()->json($subjects,200);



      }

     



     public function get_Modules_By_Section(Request $request){





        $messages = [

            'section_id.required' => 'يجب عليك إختيار القسم المراد البحث فيه',

        ];

    

        $validator = Validator::make($request->all(), [

          // 'name' => ['string', 'max:255'],

          'section_id'=>'required',

        ], $messages);



       /*********************** */

        if($validator->fails()){

             $response = array($validator->messages());

             $response = $response[0]->first();

   //echo  $test;

        //return response()->json($response, 400);

             return response()->json(['status'=>false ,'message'=> $response],400);

         }



        $modul = collect();



        $modules = Module::where('section_id',$request->section_id)->with('subSections')->get();

                 



        foreach($modules as $module){



            if($module->subSections->isNotEmpty()){



                $modul->push($module);



            }



        }

     //  $subjects = $subjects->whereNotNull('contents');

            return response()->json($modul,200);

       

     

     }

   





     public function get_subjects_by_sub_section(Request $request){



        $messages = [

            'second_sub_id.required' => 'يجب عليك إختيار القسم الفرعي المراد البحث فيه',

   

        ];

    

        $validator = Validator::make($request->all(), [

          // 'name' => ['string', 'max:255'],

          'second_sub_id'=>'required',

    

        ], $messages);



       /*********************** */

        if($validator->fails()){

             $response = array($validator->messages());

             $response = $response[0]->first();

   //echo  $test;

        //return response()->json($response, 400);

             return response()->json(['status'=>false ,'message'=> $response],400);

         }

       // $subLevel = SubLevel::where('code',$request->code_level)->first();



        $sub = collect();



        $subjects = Subject::where('second_sub_id',$request->second_sub_id)->with('contents')->get();



     

        foreach($subjects as $subject){



            if($subject->contents->isNotEmpty()){

                $sub->push($subject);

            }



        }

     //  $subjects = $subjects->whereNotNull('contents');

            return response()->json($sub,200);

      //  $spec = Speciality::where('sub_level_id',$subLevel->id)->get();   

     }



}

