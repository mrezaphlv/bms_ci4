
<!-- <div data-options="region:'north',border:false" style="height:20px;" class="header">

    <div  class="hmenu">
    </div>
</div> -->
<style>
	.tabs-with-icon{
		padding-left:0px !important;
	}
	#dropdownMenu a:hover{
		border-radius: 6px !important;
		background-color: #ffffffcc !important;
		color:blue !important;
	}
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div data-options="region:'center'">
    <div style="background-color: black;color:white;" class="easyui-layout" data-options="fit:true">
        <div class="sidebar" data-options="region:'west',collapsed:true" title="Building Management" style="width:13%;background:#23232B;">
            <div >
             <div class="m-2 position-relative" style="position: relative;">
					<div class="m-2 d-flex justify-content-between align-items-center" style="display: flex; align-items: center; position: relative;">
	
					<div id="section_profile" style="position: relative; display: flex; align-items: center; gap: 8px;">
						<img src="<?= $url_sso ?>/assets/img/avatar-2.svg" alt="Profile"
							style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; cursor: pointer;"
							onclick="toggleDropdown()" class="avatarDropdown" title="<?= $nama ?>">
						
						<span style="color:white; font-size:14px; line-height:1;cursor: pointer;" onclick="toggleDropdown()" title="<?= $nama ?>"><?= ucfirst($username) ?></span>

						<div id="dropdownMenu" style="
							display: none;
							position: absolute;
							top: 100%;
							right: 0;
							left:8px;
							color:black;
							background-color: #ffffffcc;
							border-radius: 6px;
							box-shadow: 0 2px 6px rgba(0,0,0,0.3);
							z-index: 999;
							min-width: 150px;
							margin-top: 6px;
						">
							<a href="javascript:void(0)" onclick="openProfile('<?= $url_sso ?>?tkn=<?php echo session('token') ?>&app=<?= encrypt(session('id_apps')) ?>&id=<?= session('id_user'); ?>')" 
							style="display: block; padding: 10px; color: black; text-decoration: none;">My Profile</a>
						</div>
					</div>


					<a href="javascript:void(0)" onclick="logout()" title="Logout">
						<i class="fa fa-sign-out" aria-hidden="true" style="color: white; font-size: 20px; margin-left: 10px;"></i>
					</a>

				</div>

				</div> 
            <div id="menu"></div>
            </div>
        </div>
        <div data-options="region:'center'" >
         <div   id="tabs" class="easyui-tabs" style="width:100%;height:100%">
             <div title="Home" style="padding:10px;"><h3><i class="bi bi-house-door-fill"></i></h3></div>
         </div>
        </div>
        
    </div>
</div>




<script>
function toggleDropdown() {
    const menu = document.getElementById("dropdownMenu");
    menu.style.display = menu.style.display === "block" ? "none" : "block";
}

document.addEventListener("click", function(e){
    const profile = document.getElementById("section_profile");
    const menu = document.getElementById("dropdownMenu");
    if (profile && menu && !profile.contains(e.target)) {
        menu.style.display = "none";
    }
});
</script>

<style>
		#dropdownMenu a:hover {
			background-color: #444;
		}

        .header{
            background: #23232B;
            color: #fff;
            direction: ltr;
        }
        .combo-panel panel-body panel-body-noheader{
            background-color: #23232B !important;
        }
        .panel combo-p panel-htop{
            background-color: #23232B !important;

        }
        .tabs{
            background: rgba(35,35,43,1);
        }
        .header .navbar{
            margin-bottom: 0;
            display: inline-block;
            vertical-align: middle;
        }
        .header a,.header a:hover{
            color: #fff;
            line-height: 50px;
        }
        .header .content{
            padding: 0.5em 4.5%;
        }
        .content{
            padding: 2em;
            padding-left: 5%;
            padding-right: 5%;
            max-width: 1230px;
            margin: 0 auto;
        }
        .hmenu{width:900px;margin:0 auto;font-size:22px;}
        .foot{text-align:center;font-size:12px;height:20px;padding:5px;}

        .left{width:5px;background-color: #23232B;}
        .menu{
            color:white;
        }
		
    </style>

    <script>
function logout() {
        // Redirect ke fungsi logout di controller
        window.location.href = '<?= site_url('login/logout') ?>';
    }
$(function(){
        $('#tabs').tabs({
			onBeforeClose:function(title,index){            
                if (title == 'Add Payout' || title == 'Invoice Payout'){
                    document.getElementById('indahFramepayout').contentDocument.location.reload(true);
                } else if (title == 'Pembayaran ke Tenant' || title == 'Invoice Payment'){
                    document.getElementById('indahFramepaymenttenant').contentDocument.location.reload(true);
                }
			},
		});
            var data = [{
            text: 'Item1',
            iconCls: 'icon-sum',
            state: 'open',
            children: [{
                text: 'Option1'
            },{
                text: 'Option2'
            },{
                text: 'Option3',
                children: [{
                    text: 'Option31'
                },{
                    text: 'Option32'
                }]
            }]
        },{
            text: 'Item2',
            iconCls: 'icon-more',
            children: [{
                text: 'Option4'
            },{
                text: 'Option5'
            },{
                text: 'Option6'
            }]
        }];
    $('#menu').tree({
        method: "GET",
        url: '<?= site_url('index.php/menu/get') ?>',
        loadFilter: function(data){
            if (data.d){
                return data.d;
            } else {
                return data;
            }
        },
         onLoadSuccess: function() {
                $('#menu').tree('collapseAll');
            },
        onClick: function(node){
           // alert(node.url);
            openTab(node);
           // alert(node.attributes.url);  // alert node text property when clicked
        }
    });
})

// Function to check if a tab exists by its text
function tabExists(text) {
    var tabs = $("#tabs").tabs('tabs');
    var exists = false;
    
    $.each(tabs, function(index, tab){
        if ($(tab).panel('options').title === text) {
            exists = true;
            return false; // Exit the loop early
        }
    });
    
    return exists;
}
function reloadTab(text){
	$("#tabs").tabs('select',text);
	
	var frameID = "indahFrame"+text.toLowerCase().replace(/\s/g, '');
	document.getElementById(frameID).contentWindow.location.reload();
}
function reloadTabProfile(frameID){
	document.getElementById(frameID).contentWindow.location.reload();
}

function openTab(node){
    if(node.url==undefined){
        return;
    }
    if($("#tabs").tabs("exists",node.text)){
        $("#tabs").tabs("select",node.text);
    }else{
        var frameID = "indahFrame"+node.text.toLowerCase().replace(/\s/g, '');
        var content="<iframe id='"+frameID+"' frameborder=0 scrolling='no' style='width:100%;height:100%' src='"+node.url+"'></iframe>"
        $("#tabs").tabs("add",{
            title:node.text,
            iconCls:node.iconCls,
            closable:true,
            content:content,
			tools:[{
				iconCls:'icon-mini-refresh',
				handler:function(){
					reloadTab(node.text);
				}
			}]
        });
    }
}

function openTab_forceclosed(node){
    if(node.url==undefined){
        return;
    }
    if($("#tabs").tabs("exists",node.text)){
        // $("#tabs").tabs("select",node.text);
        closeTab(node.text);
         var frameID = "indahFrame"+node.text.toLowerCase().replace(/\s/g, '');
        var content="<iframe id='"+frameID+"' frameborder=0 scrolling='no' style='width:100%;height:100%' src='"+node.url+"'></iframe>"
        $("#tabs").tabs("add",{
            title:node.text,
            iconCls:node.iconCls,
            closable:true,
            content:content,
			tools:[{
				iconCls:'icon-mini-refresh',
				handler:function(){
					reloadTab(node.text);
				}
			}]
        });
    }else{
        var frameID = "indahFrame"+node.text.toLowerCase().replace(/\s/g, '');
        var content="<iframe id='"+frameID+"' frameborder=0 scrolling='no' style='width:100%;height:100%' src='"+node.url+"'></iframe>"
        $("#tabs").tabs("add",{
            title:node.text,
            iconCls:node.iconCls,
            closable:true,
            content:content,
			tools:[{
				iconCls:'icon-mini-refresh',
				handler:function(){
					reloadTab(node.text);
				}
			}]
        });
    }
}

function closeTab(node){
    $('#tabs').tabs('close', node);
}

function openTab_inside(node){
    if(node.url==undefined){
        return;
    }
    if($("#tabs").tabs("exists",node.text)){
        $("#tabs").tabs("select",node.text);
    }else{
        var content="<iframe frameborder=0 scrolling='no' style='width:100%;height:100%' src='"+node.url+"'></iframe>"
        $("#tabs").tabs("add",{
            title:node.text,
            iconCls:node.iconCls,
            closable:true,
            content:content
        });
    }
}
function openProfile(url){
	var title = 'Profile';
	if($("#tabs").tabs("exists",title)){
        $("#tabs").tabs("select",title);
    }else{
		var frameID = "sso";
		// scrolling='no'
		var content="<iframe id='"+frameID+"' frameborder=0  style='width:100%;height:100%' src='"+url+"'></iframe>"
		$("#tabs").tabs("add",{
			title:'Profile',
			iconCls:'user-men',
			closable:true,
			content:content,
			tools:[{
				iconCls:'icon-mini-refresh',
				handler:function(){
					reloadTabProfile(frameID);
				}
			}]
		});
	}
}

function toggle(){
            var opts = $('#menu').tree('options');
            $('#menu').tree(opts.collapsed ? 'expand' : 'collapse');
            opts = $('#menu').tree('options');
            $('#menu').tree('resize', {
                width: opts.collapsed ? 60 : 200
            })
        }
function collapseAll(){
    $('#menu').tree('collapseAll');
}
function expandAll(){
    $('#menu').tree('expandAll');
}
    </script>
