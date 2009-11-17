function Smwh_Skin() {

    //this.addMenuFunctions = function
    
        this.expanded = false;
        this.treeviewhidden = true;
  
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

    this.showTreeViewRightSide = function(){
        if( this.treeviewhidden == false ){
            this.treeviewhidden = true;
             $jq("#smwh_treeview").css("display", "none");
        } else {
            this.treeviewhidden = false;
            $jq("#smwh_treeview").css("display", "block");
            $jq("#smwh_treeview").css("width", "auto");
            $jq("#smwh_treeview").removeClass("smwh_treeviewleft");
            $jq("#smwh_treeview").addClass("smwh_treeviewright");
        }
    };

    this.showTreeViewLeftSide = function(){
        if( this.treeviewhidden == false ){
            this.treeviewhidden = true;
             $jq("#smwh_treeview").css("display", "none");
        } else {
            this.treeviewhidden = false;
            $jq("#smwh_treeview").css("display", "block");
            var contentoffset = $jq("#shadows").offset().left - 5;
            $jq("#smwh_treeview").css("width", contentoffset+"px");
            $jq("#smwh_treeview").removeClass("smwh_treeviewright");
            $jq("#smwh_treeview").addClass("smwh_treeviewleft");
        }
    };

    this.resizeMainTable = function(){
        var windowheight = $jq(window).height()

        $jq("#smwh_HeightShell").css("min-height", windowheight+"px");
    }
    
    if(typeof GeneralBrowserTools != 'undefined'){
        var state = GeneralBrowserTools.getCookieObject("smwSkinExpanded");
        if (state == true){
            this.expanded = true;
            $jq("#shadows").css("width", "100%");
        }

    }
        
    $jq(".smwh_menulistitem").hover(this.showMenu, this.hideMenu);
    $jq("#smwh_treeviewtoggleright").click(this.showTreeViewRightSide.bind(this));
    $jq("#smwh_treeviewtoggleleft").click(this.showTreeViewLeftSide.bind(this));
    $jq(window).resize(this.resizeMainTable.bind(this));
}

var smwh_Skin;

$jq(document).ready(function(){
    smwh_Skin = new Smwh_Skin();
    smwh_Skin.resizeMainTable();
}
);

