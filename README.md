# Heart Transplant

---

##<span style="color:red">New Transplant Entry</span>
***


### The fields that are collected in this form are:
Field label | REDCap variable | Value type
--- | --- | ---
Stanford Medical Record Number | stanford_mrn
Date of Transplant | dot
First Name | first_name
Last Name | last_name
State | address_state
Zip | address_zip
Gender of Recipient | sex_r
Date of Birth | dob
Did the patient receive a kidney transplant? | dem_kidney_tx
Date of Kidney Transplant | dem_kidney_tx_date
Did the patient receive a liver transplant? | dem_liver_tx
Date of Liver Transplant|dem_liver_tx_date
UNOS ID | unos_id
MATCH ID | match_id
Donor Age | age_d
Gender of Donor | sex_d
Meets CDC Guidelines for High Risk | donor_high_risk | See coding table below
Cause of Death | dnr_cause_death | See coding table below
---

## Coded value mappings

### Cause of Death : dnr_cause_death
Code | Label 
--- | --- 
1 |	MVA
2|  GSW
3|	Child Abuse (shaking)
4|	Drowning
5|	Blunt Head Trauma (Other)
6|	Intracranial Hemmorhage
8|	Infection
10|	Ischemia (other, like seizure)
98|	Unknown
99|	Other (describe below)


---
### 	Meets CDC Guidelines for High Risk : donor_high_risk
Code | Label 
--- | --- 
1 |	Yes
0 |	No
98 |Unknown

###Data Entry Checks

---
### There are some data entry checks in place. If it does not pass the expected behavior, it will not save and warn the user.
Check | Expected behavior 
--- | --- 
MRN number | MRN must be all numeric
MRN + Transplant Date | MRN  and Transplant Date must be unique in the database.

---
___


##<span style="color:red">Edit Death Data</span>
 ***
### The fields that are collected in this form are:
Field label | REDCap variable | Value type
--- | --- | ---
Stanford Medical Record Number | stanford_mrn
Date of Transplant | dot
Cause of Death | r_mode_death | See coding table below
Date of Death | dem_date_of_death
---

## Coded value mappings

### Cause of Death : r_mode_death
Code | Label 
--- | --- 
1 |	Acute Cellular Rejection
2 |	Antibody Mediated rejection
3 |	Primary Graft Dysfunction
4 |	Infection
5 |	Non- Cardiac/Transplant Related
6 |	Cardiac Allograft Vasculopathy
7 |	Unknown sudden cardiac death
8 |	Complications of malignancy
9 |	Surgical Complications
10 |	Pulmonary Disease/Respiratory Failure
11 |	Pulmonary Embolus
12 |	Multiorgan failure
13 |	Stroke/CVD
99 |	Other


### Data Entry Checks


### There are some data entry checks in place. If it does not pass the expected behavior, it will not save and warn the user.
Check | Expected behavior 
--- | --- 
MRN + Transplant Date | MRN  and Transplant Date must exist already in the database
1 match | If more than one match is found, it will edit the first match and user notified of the multiple matches

---
___


##<span style="color:red">Annual Update</span>
 ***
### The fields that are collected in this form are:
Field label | REDCap variable | Value type
--- | --- | ---
Stanford Medical Record Number | stanford_mrn
Date of Followup | last_folloupw_date
Annual Year | not saved| Used to determine which update
Was the patient started on dialysis THIS PAST YEAR | R_followup_dialysis
Date of Dialysis Initiation | r_post_dialysis_date
Did the patient undergo ICD implantation THIS PAST YEAR? |post_icd
Date of ICD implantation | post_icd_date
Did the patient undergo PPM implantation THIS PAST YEAR? | post_ppm
Date of PPM implantation | post_ppm_date
Has the patient had a PTLD diagnosis THIS PAST YEAR? | mal_ptld_yn
Date of PTLD | mal_date_ptld
Date of Malignancy | mal_date_sot
Type of Malignancy | post_mal_type
Has the patient had a solid organ tumor diagnosis THIS PAST YEAR | sot_type
Has the patient had a MELANOMA diagnosis THIS PAST YEAR | mal_melanoma
Date of melanoma diagnosis | mal_mel_date
Will the patient undergo DSE or Angiogram as part of ischemia testing | 
Date of Angiogram | rt_cth_date_angiogram
Any vessel stenosis present | Rt_cth_vessel_angiogram
MIT if known | rt_cth_mit
Date of DSE | rt_ech_date
Results of DSE | rt_ech_dse
LVEF on DSE | rt_ech_lvef
What immunosuppressants is patient taking? | Immuno_XX_mo
Type of Echo | rt_ech_type
---

## Coded value mappings

###  immuno_XX_mo : immuno_XX_mo
Code | Label 
--- | --- 
1    | tacrolimus (IR)
2	 |	envarsus
3	 |	astagraf
4	 |	cellcept
5	 |	myfortic
6	 |	Pred > 5 mg/day
7	 |	Pred <= 5 mg/day
8	 |	Siro
9	 |	Evero
10	 |	AZA
12	 |	CSA


### Data Entry Checks


### There are some data entry checks in place. If it does not pass the expected behavior, it will not save and warn the user.
Check | Expected behavior 
--- | --- 
MRN + Last Name | MRN  and Last Name must exist already in the database. The most recent dot will be selected and user notified of the year of transplant


