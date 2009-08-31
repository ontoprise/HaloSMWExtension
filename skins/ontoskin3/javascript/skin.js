 function Smwh_Menu() {

    //this.addMenuFunctions = function
    
  
    this.showMenu = function(){
        $jq(this).addClass("hovering");
    };

    this.hideMenu = function(){
        $jq(this).removeClass("hovering");
    };

    $jq(".smwh_menulistitem").hover(this.showMenu, this.hideMenu);

}

$jq(document).ready(function(){
        var smwh_Menu = new Smwh_Menu();
    }
);


