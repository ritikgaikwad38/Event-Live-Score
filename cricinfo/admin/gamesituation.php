<?php include'core/init.php';?>
<?php
if (!Session::exists('id') && !Session::exists('name') )
{
  header('Location: ' . 'login.php'); 
}
if(Session::get('role')!='admin')
{
   header('Location: ' . 'index.php');  
}
$id=Session::get('id');
//echo $id;
$sql="SELECT match_id FROM m_atch WHERE adminid=$id";
$result=DB::getConnection()->select($sql);
//var_dump($result);
if(!$result)
{
  header('Location: ' . 'index.php');
}
class gameSituation
{
  
	private $adminid;
  private $matchid;
	private $tossId;
  private $teamAwicket;
  private $teamBwicket;
  private $teamArun;
  private $teamBrun;
  private $teamAball;
  private $teamBball;
  private $batting;
  private $bowling;
  private $over;
  private $bowlerball;
  private $striker;
  private $nonstriker;

	public function game()
	{
  
    $this->teamAwicket=0;
    $this->teamBwicket=0;
    $this->teamArun=0;
    $this->teamBrun=0;
    $this->teamAball=0;
    $this->teamBball=0;
    $this->batting=0;
    $this->bowling=0;
    $this->over=0;
    $this->bowlerball=0;

    $this->adminid=Session::get('id');
	  $sql="SELECT * FROM m_atch WHERE adminid=$this->adminid";
    $result=DB::getConnection()->select($sql);
    if($result)
    {
      foreach ($result as $value) 
      {
       	$this->tossId=$value['toss'];
       	$this->matchid=$value['match_id'];
        $this->over=$value['overs'];
      }
    }
    //echo $this->tossId."<br>";
    $sql="SELECT * FROM status WHERE match_id=$this->matchid AND toss=$this->tossId";
    $result=DB::getConnection()->select($sql);
    if($result)
    {
       foreach ($result as $value) 
       {
         if($value['out_type']=='not out')
         {
           $this->batting+=1;
         }
         $this->teamBrun+=$value['bowlruns'];
         $this->teamBball+=$value['bowled_overs'];
         $this->teamBwicket+=$value['wicket'];
         $this->teamBwicket+=$value['extra_wicket'];
       }
    }

    $sql="SELECT status_id FROM status WHERE match_id=$this->matchid AND toss=$this->tossId AND stricking_role=1";
    $result=DB::getConnection()->select($sql);
    if($result)
    {
       foreach ($result as $value) 
       {
            $this->striker= $value['status_id']; 
       }
    }
    $sql="SELECT status_id FROM status WHERE match_id=$this->matchid AND toss=$this->tossId AND stricking_role=2";
    $result=DB::getConnection()->select($sql);
    if($result)
    {
       foreach ($result as $value) 
       {
            $this->nonstriker= $value['status_id']; 
       }
    }
   // echo $this->batting;

    $sql="SELECT * FROM status WHERE match_id=$this->matchid AND toss!=$this->tossId";
    $result=DB::getConnection()->select($sql);
    if($result)
    {
       foreach ($result as $value) 
       {
         
         $this->bowlerball=$value['bowled_overs'];
         $this->teamArun+=$value['bowlruns'];
         $this->teamAball+=$value['bowled_overs'];
         $this->teamAwicket+=$value['wicket'];
         $this->teamAwicket+=$value['extra_wicket'];
       }
    }
   // echo $this->batting." ".$this->bowlerball."<br>";
    if($this->teamArun>$this->teamBrun && $this->teamBball>0)
    {
       header("Location:gamefinished.php");
    }
    else if($this->teamAwicket==10 && $this->teamBball>0 || $this->teamAball==$this->over*6 && $this->teamBball>0)
    {
        header("Location:gamefinished.php");
    }
    else if($this->teamAwicket==10 || $this->teamAball==$this->over*6)
    {
      header("Location:unsetplayer.php");
    }
    else if($this->batting==1 && $this->bowlerball==6)
    {
       $sql="UPDATE status SET stricking_role=NULL WHERE match_id=$this->matchid AND toss!=$this->tossId AND stricking_role=1";
       $result=DB::getConnection()->update($sql);
       header("Location:selectbatsmanbowler.php");
    }
    else if($this->batting==1)
    {
      header("Location:selectbatsman.php");
    }
    else if($this->bowlerball==6)
    {
      $sql="UPDATE status SET stricking_role=NULL WHERE match_id=$this->matchid AND toss!=$this->tossId AND stricking_role=1";
      $result=DB::getConnection()->update($sql);

      $sql="UPDATE status SET stricking_role=2 WHERE status_id=$this->striker";
      $result=DB::getConnection()->update($sql);

      $sql="UPDATE status SET stricking_role=1 WHERE status_id=$this->nonstriker";
      $result=DB::getConnection()->update($sql);
      header("Location:lastbowler.php");
    }
    else if($this->bowlerball<6 && $this->batting==2)
    {
       header("Location:ballbyball.php");
    }
    
   
       
	}
}
$run=new gameSituation();
$run->game();
?>