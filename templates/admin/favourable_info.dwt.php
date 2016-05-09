<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.favourable_info.init();
</script>
<!-- {/block} -->


<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		{if $action_link} <a class="btn plus_or_reply data-pjax" href="{$action_link.href}" id="sticky_a"><i class="fontello-icon-reply"></i>{$action_link.text}</a> {/if}
	</h3>
</div>

<form id="form-privilege" class="form-horizontal" name="theForm" action="{$form_action}" method="post" data-edit-url="{url path='user/admin/edit'}" >
	<fieldset>
		<div class="row-fluid editpage-rightbar edit-page">
			<div class="left-bar">
				<div class="control-group" >
					<label class="control-label">{$lang.label_act_name}</label>
					<div class="controls">
						<input type="text" name="act_name" id="act_name" value="{$favourable.act_name}" size="40" class="w350"  />
						<span class="input-must">{$lang.require_field}</span> 
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">{t}优惠活动时间{/t}</label>
					<div class="controls">
						<div class="controls-split">
							<div class="ecjiaf-fl wright_wleft">
								<input name="start_time" class="date wspan12" type="text" placeholder="{t}请选择活动开始时间{/t}" value="{$favourable.start_time}"/>
							</div>
							<div class="ecjiaf-fl p_t5 wmidden">{t}至{/t}</div>
							<div class="ecjiaf-fl wright_wleft">
								<input name="end_time" class="date wspan12" type="text" placeholder="{t}请选择活动结束时间{/t}" value="{$favourable.end_time}"/>
							</div>
						</div>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">{$lang.label_user_rank}</label>
  					<div class="controls chk_radio">
						{foreach from=$user_rank_list item=user_rank}
	   						<input type="checkbox" name="user_rank[]" value="{$user_rank.rank_id}" {if $user_rank.checked}checked="true"{/if} />
	   						{$user_rank.rank_name}
   						{/foreach}
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label">{$lang.label_min_amount}</label>
  					<div class="controls">
						<input class="w350" name="min_amount" type="text" id="min_amount" value="{$favourable.min_amount}">
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">{$lang.label_max_amount}</label>
  					<div class="controls">
						<input class="w350" name="max_amount" type="text" id="max_amount" value="{$favourable.max_amount}">
						<span class="help-block">{$lang.notice_max_amount}</span>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
					    {if $favourable.act_id eq ''}
					    	<button class="btn btn-gebo" type="submit">{$lang.button_submit}</button>
		                {else}
		                	<button class="btn btn-gebo" type="submit">{t}更新{/t}</button>
		                {/if}
						<input type="hidden" name="act" value="{$form_action}" />
						<input type="hidden" name="id" id="isok" value="{$favourable.act_id}" /> 
						<input type="hidden" name="old_actname" value="{$favourable.act_name}" />
					</div>
				</div>
			</div>
			<div class="right-bar move-mod">
				<div class="foldable-list move-mod-group">
					<div class="accordion-group">
						<div class="accordion-heading">
							<a class="accordion-toggle acc-in move-mod-head" data-toggle="collapse" data-target="#telescopic1"><strong>{t}优惠活动范围{/t}</strong></a>
						</div>
						<div class="accordion-body in in_visable collapse" id="telescopic1">
							<div class="accordion-inner">
								<div class="control-group-small">
				  					<div class="edit-page">
										<select name="act_range" id="act_range_id">
											<option value="0" selected="selected" {if $favourable.act_range eq 0}selected="selected"{/if}>{$lang.far_all}</option>
											<option value="1" {if $favourable.act_range eq 1}selected="selected"{/if}>{t}指定分类{/t}</option>
											<option value="2" {if $favourable.act_range eq 2}selected="selected"{/if}>{t}指定品牌{/t}</option>
											<option value="3" {if $favourable.act_range eq 3}selected="selected"{/if}>{t}指定商品{/t}</option>
								        </select>
									</div>
									<div class="m_t10 choose_list" id="range_search" {if $favourable.act_range eq 0} style="display:none"{/if} >
										<input name="keyword" type="text" id="keyword" placeholder="{t}输入关键字进行搜索{/t}">
										<button class="btn" type="button" id="search" data-url='{url path="favourable/admin/search"}'>{$lang.button_search}</button>
							    	</div>
						    		<ul id="range-div" {if $act_range_ext}style="display:block;"{/if}>
								      	<!-- {foreach from=$act_range_ext item=item} -->
									      	<li>
										      	<input name="act_range_ext[]" type="hidden" value="{$item.id}"  />
										      	{$item.name} 
										      	<a href="javascript:;" class="delact1"><i class="fontello-icon-minus-circled ecjiafc-red"></i></a>
									      	</li>
								      	<!-- {/foreach} -->
							      	</ul>
									<div class="m_t15" id="selectbig1" style="display:none">
										<select name="result" id="result" class="w300 noselect" size="10">
										</select>
									</div>						    	
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="foldable-list move-mod-group">
					<div class="accordion-group">
						<div class="accordion-heading">
							<a class="accordion-toggle acc-in move-mod-head" data-toggle="collapse" data-target="#telescopic2"><strong>{t}优惠活动方式{/t}</strong></a>
						</div>
						<div class="accordion-body in in_visable collapse" id="telescopic2">
							<div class="accordion-inner">
								<div class="control-group-small">
					  				<div class="edit-page">
										<select name="act_type" id="act_type_id" class="" >
											<option value="0" {if $favourable.act_type eq 0}selected="selected"{/if}>{$lang.fat_goods}</option>
											<option value="1" {if $favourable.act_type eq 1}selected="selected"{/if}>{$lang.fat_price}</option>
											<option value="2" {if $favourable.act_type eq 2}selected="selected"{/if}>{$lang.fat_discount}</option>
								        </select>
										<input class="f_r w70" name="act_type_ext" type="text" id="act_type_ext" value="{$favourable.act_type_ext}" />
									</div>
									<div class="m_t5 m_b5 clear">
								        <span class="help-block">
											{t}当优惠方式为“享受赠品（特惠品）”时，请输入允许买家选择赠品（特惠品）的最大数量，数量为0表示不限数量；
											当优惠方式为“享受现金减免”时，请输入现金减免的金额； 
											当优惠方式为“享受价格折扣”时，请输入折扣（1－99），如：打9折，就输入90。{/t} 
										</span>
									</div>
									<div class="choose_list" id="type_search"{if $favourable.act_type neq 0} style="display:none"{/if}>
										<div class="control-group m_t10" >
									    	<input name="keyword1" type="text" id="keyword1"  placeholder="{t}输入特惠品的关键字进行搜索{/t}" />
									    	<button type="button" id="search1" class="btn" data-url='{url path="favourable/admin/search"}'>{$lang.button_search}</button>
									    </div>
									    <div id="gift-div" {if $favourable.gift}class="m_b15"{/if}>
				                            <table id="gift-table" >
										      <!-- {if $favourable.gift} -->
										        <tr align="center"><td><strong>{$lang.js_languages.gift}</strong></td><td><strong>{$lang.js_languages.price}</strong></td></tr>
										        <!-- {foreach from=$favourable.gift item=goods key=key} -->
										        <tr align="center">
										        	<td>
										        		<input type="hidden" name="gift_id[{$key}]" value="{$goods.id}" />{$goods.name}
										        	</td>
											        <td>
												        <input name="gift_price[{$key}]" type="text"  value="{$goods.price}" size="10" class="w150" />
												        <input name="gift_name[{$key}]"  type="hidden" value="{$goods.name}" />
												        <a href="javascript:;" class="delact "><i class="fontello-icon-minus-circled ecjiafc-red"></i></a>
											        </td>
										        </tr>
										        <!-- {/foreach} -->
									   		 <!-- {/if} -->
								     	 	</table>
								    	</div>
									    <div id="selectbig" style="display:none">
										    <select name="result1" id="result1" class="w300 noselect" size="10">
										    </select>
									    </div>
							    	</div>						    	
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
</form>
<!-- {/block} -->