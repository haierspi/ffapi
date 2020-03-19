<?php
namespace common\wxmp;

/**
 * error code 说明.
 * <ul>
 *    <li>-40001: 签名验证错误</li>
 *    <li>-40002: xml解析失败</li>
 *    <li>-40003: sha加密生成签名失败</li>
 *    <li>-40004: encodingAesKey 非法</li>
 *    <li>-40005: appid 校验错误</li>
 *    <li>-40006: aes 加密失败</li>
 *    <li>-40007: aes 解密失败</li>
 *    <li>-40008: 解密后得到的buffer非法</li>
 *    <li>-40009: base64加密失败</li>
 *    <li>-40010: base64解密失败</li>
 *    <li>-40011: 生成xml失败</li>
 * </ul>
 */
class ErrorCodeMiniProgram
{
	public static $OK = 0;
	public static $IllegalAesKey = -41001;
	public static $IllegalIv = -41002;
	public static $IllegalBuffer = -41003;
	public static $DecodeBase64Error = -41004;
}
