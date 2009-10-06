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

            //store state in a cookie
            if(GeneralBrowserTools!=undefined){
                GeneralBrowserTools.setCookieObject("smwSkinExpanded", this.expanded);
            }
        } else {
            $jq("#shadows").css("width", "960px");
            this.expanded = false;

            //store state in a cookie
            if(GeneralBrowserTools!=undefined){
                GeneralBrowserTools.setCookieObject("smwSkinExpanded", this.expanded);
            }

        }
    };
    
    if(typeof GeneralBrowserTools != 'undefined'){
        var state = GeneralBrowserTools.getCookieObject("smwSkinExpanded");
        if (state == true){
            this.expanded = true;
            $jq("#shadows").css("width", "100%");
        }

    }
        
    $jq(".smwh_menulistitem").hover(this.showMenu, this.hideMenu);

}

var smwh_Skin;

$jq(document).ready(function(){
    smwh_Skin = new Smwh_Menu();
}
);


