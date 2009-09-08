 function Smwh_Menu() {

    //this.addMenuFunctions = function

    this.expanded = false;
  
    this.showMenu = function(){
        $jq(this).addClass("hovering");
    };

    this.hideMenu = function(){
        $jq(this).removeClass("hovering");
    };

    this.expandPage = function (){
        if( this.expanded == false){
            $jq("#shadows").css("width", "100%");
            this.expanded = true;
        } else {
            $jq("#shadows").css("width", "960px");
            this.expanded = false;
        }
        
    };

    $jq(".smwh_menulistitem").hover(this.showMenu, this.hideMenu);

}

var smwh_Skin;

$jq(document).ready(function(){
        smwh_Skin = new Smwh_Menu();
    }
);


