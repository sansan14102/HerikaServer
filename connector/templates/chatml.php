<?php

            $GLOBALS["more_stopseq"][]="<|im_start|>";
            $context="<|im_start|>system\n{$GLOBALS["PROMPT_HEAD"]}\n";
            $context.="{$GLOBALS["HERIKA_PERS"]}\n";
            $context.="{$GLOBALS["COMMAND_PROMPT"]}\n#CONTEXT\n";
            $GLOBALS["DEBUG_DATA"][]=$context;

            $contextHistory="";
            $n=0;
            foreach ($contextData as $s_role=>$s_msg) {	// Have to mangle context format

                if ($n==(sizeof($contextData)-1)) {   // Last prompt line

                    $instruction.=$s_msg["content"]."<|im_end|>\n";

                } else if ($n==(sizeof($contextData)-2)) {   // Last prompt line

                    $instruction="<|im_end|>\n<|im_start|>user\n".$s_msg["content"];

                }  else {
                        if ($s_msg["role"]=="user") {
                            $contextHistory.=$s_msg["content"]."\n";
                            $GLOBALS["DEBUG_DATA"][]=$s_msg["content"];
                    
                            
                        } else if ($s_msg["role"]=="assistant") {
                
                            if (isset($s_msg["tool_calls"])) {
                                    $pb["system"].="{$GLOBALS["HERIKA_NAME"]} issued ACTION {$s_msg["tool_calls"][0]["function"]["name"]}";
                                    $lastAction="{$GLOBALS["HERIKA_NAME"]} issued ACTION {$s_msg["tool_calls"][0]["function"]["name"]} {$s_msg["tool_calls"][0]["function"]["arguments"]}";
                                    
                                    $localFuncCodeName=getFunctionCodeName($s_msg["tool_calls"][0]["function"]["name"]);
                                    $localArguments=json_decode($s_msg["tool_calls"][0]["function"]["arguments"],true);
                                    $lastAction=strtr($GLOBALS["F_RETURNMESSAGES"][$localFuncCodeName],[
                                                    "#TARGET#"=>current($localArguments),
                                                    ]);
                                    
                                    
                                } else {
                                    $GLOBALS["DEBUG_DATA"][]=$s_msg["content"];
                                    //$contextHistory.=$s_msg["content"]."\n";
                                    
                                    $dialogueTarget=extractDialogueTarget($s_msg["content"]);
                                    // Trying to provide examples
                                    $contextHistory.="<|im_end|>\n <|im_start|>assistant{\"character\": \"{$GLOBALS["HERIKA_NAME"]}\", \"listener\": \"{$dialogueTarget["target"]}\", \"mood\": \"\", \"action\": \"\", 
                                                        \"target\": \"\", \"message\": \"".trim($dialogueTarget["cleanedString"])."\"}<|im_end|>\n <|im_start|>user\n";
                                }
                    
                    } else if ($s_msg["role"]=="tool") {
                        $pb["system"].=$element["content"]."\n";
                        $contextHistory.="The Narrator:".strtr($lastAction,["#RESULT#"=>$s_msg["content"]]);
                        $GLOBALS["PATCH_STORE_FUNC_RES"]=strtr($lastAction,["#RESULT#"=>$s_msg["content"]]);
                        
                    } else if ($s_msg["role"]=="system") {
                        }  // Must rebuild this


                }

                $n++;
            }

            $context.="$contextHistory  $instruction <|im_start|>assistant\n";
            $GLOBALS["DEBUG_DATA"][]="$instruction";
            $GLOBALS["DEBUG_DATA"]["prompt"]=$context;
            
            ?>
