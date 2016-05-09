<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.favourable_list.init();
</script>
<!-- {/block} -->

    <!-- {block name="main_content"} -->
	<div>
		<h3 class="heading">
			<!-- {if $ur_here}{$ur_here}{/if} -->
			<!-- {if $action_link} -->
			<a class="btn plus_or_reply data-pjax" href="{$action_link.href}"  id="sticky_a"><i class="fontello-icon-plus"></i>{$action_link.text}</a>
			<!-- {/if} -->
		</h3>
	</div>

	<!-- 批量操作和搜索 -->
	<div class="row-fluid batch" >
		<form method="post" action="{$search_action}" name="searchForm">
			<div class="btn-group f_l m_r5">
				<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
					<i class="fontello-icon-cog"></i>{t}批量操作{/t}
					<span class="caret"></span>
				</a>
				<ul class="dropdown-menu">
					<li><a class="button_remove" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{url path='favourable/admin/batch'}" data-msg="您确定要这么做吗？" data-noSelectMsg="请先选中要删除的优惠活动！" data-name="act_id" href="javascript:;"><i class="fontello-icon-trash"></i>{t}删除优惠活动{/t}</a></li>
				</ul>
			</div>
			<div class="choose_list f_r">
				<input type="text" name="keyword" value="{$filter.keyword}" placeholder="请输入优惠活动名称"/> 
				<span class="p_t3"><input type="checkbox" name="is_going" {if $filter.is_going}checked{/if} id="is_going" value="1" /></span>
				<span>{$lang.act_is_going}</span>
				<input class="btn search_articles" type="submit" value="搜索">
			</div>
		</form>
	</div>
	
	<div class="row-fluid">
		<div class="span12">
			<form method="POST" action="{$form_action}" name="listForm">
				<div class="row-fluid">
					<table class="table table-striped smpl_tbl table-hide-edit">
						<thead>
							<tr>
							    <th class="table_checkbox"><input type="checkbox" name="select_rows" data-toggle="selectall" data-children=".checkbox"/></th>
							    <th class="w200">{t}商家名称{/t}</th>
							    <th class="w200">{t}优惠活动名称{/t}</th>
							    <th class="w150">{t}开始时间{/t}</th>
							    <th class="w150">{t}结束时间{/t}</th>
							    <th class="w100">{t}金额下限{/t}</th>
							    <th class="w100">{t}金额上限{/t}</th>
							    <th class="w100">{t}排序{/t}</th>
						  	</tr>
						</thead>
						<!-- {foreach from=$favourable_list.item item=favourable} -->
					    <tr>
					    	<td><span><input type="checkbox" class="checkbox" value="{$favourable.act_id}" name="checkboxes[]" ></span></td>
					    	<td>
					    		<!-- {if $favourable.shop_name} -->
								<font style="color:#F00;">{$favourable.shop_name}</font>
								<!-- {else} -->
								<font style="color:#0e92d0;">{t}自营{/t}</font>
								<!-- {/if} -->
							</td>
					      <td class="hide-edit-area">
					      <span class="cursor_pointer" data-trigger="editable" data-url="{RC_Uri::url('favourable/admin/edit_act_name')}" data-name="act_name" data-pk="{$favourable.act_id}" data-title="{t}编辑优惠活动名称{/t}">{$favourable.act_name}</span>
				     	  <div class="edit-list">
							  <a class="data-pjax" href='{url path="favourable/admin/edit" args="act_id={$favourable.act_id}"}'  title="{t}编辑{/t}">{t}编辑{/t}</a>&nbsp;|&nbsp;
					          <a class="ajaxremove ecjiafc-red" data-toggle="ajaxremove" data-msg="{t}您确定要删除该优惠活动吗？{/t}" href='{url path="favourable/admin/remove" args="act_id={$favourable.act_id}"}' title="{t}移除{/t}">{t}删除{/t}</a>
				    	  </div>
					      </td>
					      <td>{$favourable.start_time}</td>
					      <td>{$favourable.end_time}</td>
					      <td>{$favourable.min_amount}</td>
					      <td>{$favourable.max_amount}</td>
					      <td><span class="edit_sort_order cursor_pointer" data-trigger="editable" data-url="{RC_Uri::url('favourable/admin/edit_sort_order')}" data-name="sort_order" data-pk="{$favourable.act_id}"  data-title="{t}编辑优惠活动排序{/t}">{$favourable.sort}</span></td>
					    </tr>
					    <!-- {foreachelse} -->
				        <tr><td class="no-records" colspan="10">{t}没有找到任何记录{/t}</td></tr>
						<!-- {/foreach} -->
			            </tbody>
			         </table>
			         <!-- {$favourable_list.page} -->
		         </div>
	         </form>
         </div>
	</div>
<!-- {/block} -->