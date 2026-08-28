<div class="box">
       
<header style="font-size: 18px;color: #00297c;font-weight: 700;"> Categories</header>
          <section class="ar-flags">

                 <?php
                 $view_category = "select * from product_category_arabyos  where pc_parent_id= '0'";
                        $run= mysql_query($view_category);
                        //var_dump($run_sql);
                       
                       // $rows=mysql_fetch_array($run_query);
                        //var_dump(mysql_fetch_array($run_query));
                        //if(mysql_num_rows($run_query))
                        //{
                        $counter=0;
                        
                         while( $row=mysql_fetch_array($run, MYSQL_ASSOC))
                            { 
                             $counter++;
                            
                if($counter== 5)
                {
                    echo'<div class="collapse" id="categories" >';
                }
                ?>
                
              <div class="checkbox">
                <label>
                    <input type="checkbox" class="search_filter" name="category_id[]" value="<?php echo $row['pc_id']; ?>">
                  <span><?php echo $row['pc_name']; ?></span> </label>
              </div>
                <?php } ?>
                    <div class="text-right"> <a class="btn btn-link" type="button" data-toggle="collapse" data-target="#categories" aria-expanded="false" aria-controls="collapseExample"> + View More </a> </div>

          </section>
              </div>

