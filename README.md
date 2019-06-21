# Heart Transplant

---

## New Transplant Entry
***


### The fields that are collected in this form are:
Field label | REDCap variable | Value type
--- | --- | ---
Stanford Medical Record Number | mrn_fix
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

### Data Entry Checks

---
### There are some data entry checks in place. If it does not pass the expected behavior, it will not save and warn the user.
Check | Expected behavior 
--- | --- 
MRN number | MRN must be all numeric
MRN + Transplant Date | MRN  and Transplant Date must be unique in the database.

---
___


##Edit Death Data
 ***
### The fields that are collected in this form are:
Field label | REDCap variable | Value type
--- | --- | ---
Stanford Medical Record Number | mrn_fix
Date of Transplant | dot
Cause of Death | out_mode_death | See coding table below
Date of Death | dem_date_of_death
---

## Coded value mappings

### Cause of Death : out_mode_death
Code | Label 
--- | --- 
1 |	Cellular Rejection
2|  AMR
3|	PGD
4|	Infection
5|	Non-Cardiac/Transplant Related
6|	CAV
7|	Unknown sudden cardiac death
8|Complications of malignancy
9|Surgical Complications
10|Pulmonary Disease/Respiratory Failure
11|Pulmonary Embolus
99|Other


### Data Entry Checks


### There are some data entry checks in place. If it does not pass the expected behavior, it will not save and warn the user.
Check | Expected behavior 
--- | --- 
MRN + Transplant Date | MRN  and Transplant Date must exist already in the database
1 match | If more than one match is found, it will edit the first match and user notified of the multiple matches

