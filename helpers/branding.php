<?php

/**
 * Builds an organization's branding bits for transaction PDFs from its profile
 * row (organizations table). No dependencies beyond the array passed in.
 */
function org_branding($orgRow): array {

	$orgRow = is_array($orgRow) ? $orgRow : [];

	$hex = (isset($orgRow["themeColor"]) && preg_match('/^#[0-9a-fA-F]{6}$/', $orgRow["themeColor"]))
		? $orgRow["themeColor"] : "#1e3a5f";

	$name = htmlspecialchars($orgRow["name"] ?? "Company");

	$locality = trim(($orgRow["city"] ?? "") . " " . ($orgRow["region"] ?? "") . " " . ($orgRow["postalCode"] ?? ""));
	$addrParts = array_filter([$orgRow["address"] ?? "", $locality, $orgRow["country"] ?? ""], fn($x) => trim((string)$x) !== "");
	$address = htmlspecialchars(implode(", ", $addrParts));

	$contactParts = array_filter([
		!empty($orgRow["phone"])   ? "Tel: " . $orgRow["phone"] : "",
		!empty($orgRow["fax"])     ? "Fax: " . $orgRow["fax"]   : "",
		$orgRow["email"]   ?? "",
		$orgRow["website"] ?? "",
	], fn($x) => trim((string)$x) !== "");
	$contact = htmlspecialchars(implode("  |  ", $contactParts));

	$logo = (!empty($orgRow["logo"]) && is_file($orgRow["logo"])) ? $orgRow["logo"] : "";

	return [
		"hex"     => $hex,
		"r"       => hexdec(substr($hex, 1, 2)),
		"g"       => hexdec(substr($hex, 3, 2)),
		"b"       => hexdec(substr($hex, 5, 2)),
		"name"    => $name,
		"address" => $address,
		"contact" => $contact,
		"logo"    => $logo,
	];
}
